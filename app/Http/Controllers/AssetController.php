<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssetRequest;
use App\Models\Asset;
use App\Models\Category;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        // Semua aksi kecuali index dan show hanya untuk admin
        return [new Middleware('admin', except: ['index', 'show'])];
    }

    public function index(Request $request)
    {
        $assets = Asset::with('category')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_code', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('assets.index', compact('assets', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('assets.create', compact('categories'));
    }

    public function store(AssetRequest $request)
    {
        $data = $request->validated();
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('assets', 'public');
        }

        if (blank($data['asset_code'] ?? null)) {
            $category = Category::findOrFail($data['category_id']);

            // Kalau dua admin menyimpan bersamaan dan nomornya bentrok, ulangi dengan nomor berikutnya
            $asset = retry(
                3,
                fn () => Asset::create(array_merge($data, ['asset_code' => Asset::nextCodeFor($category)])),
                50,
                fn ($e) => $e instanceof UniqueConstraintViolationException
            );
        } else {
            $asset = Asset::create($data);
        }

        return redirect()->route('assets.index')
            ->with('success', "Aset berhasil ditambahkan dengan kode {$asset->asset_code}.");
    }

    public function nextCode(Request $request)
    {
        $validated = $request->validate(['category_id' => ['required', 'exists:categories,id']]);

        return response()->json([
            'code' => Asset::nextCodeFor(Category::findOrFail($validated['category_id'])),
        ]);
    }

    public function show(Request $request, Asset $asset)
    {
        $isAdmin = $request->user()->isAdmin();

        // Staff tidak boleh melihat aset yang sudah dihapus
        if ($asset->trashed() && ! $isAdmin) {
            abort(404);
        }

        $asset->load('category');

        // Riwayat peminjaman hanya untuk admin (memuat nama peminjam lain)
        $loans = $isAdmin
            ? $asset->loans()->with('user')->latest('loan_date')->latest('id')->take(20)->get()
            : collect();

        return view('assets.show', compact('asset', 'loans'));
    }

    public function trashed(Request $request)
    {
        $assets = Asset::onlyTrashed()
            ->with('category')
            ->withCount('loans')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_code', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('assets.trashed', compact('assets'));
    }

    public function restore(Asset $asset)
    {
        if (! $asset->trashed()) {
            return redirect()->route('assets.index');
        }

        $asset->restore();

        return redirect()->route('assets.index')
            ->with('success', "Aset {$asset->name} berhasil dipulihkan.");
    }

    public function forceDestroy(Asset $asset)
    {
        if (! $asset->trashed()) {
            abort(404);
        }

        // Riwayat peminjaman harus tetap utuh, jadi aset yang pernah dipinjam tidak boleh dihapus permanen
        if ($asset->loans()->exists()) {
            return back()->with('error', 'Aset ini punya riwayat peminjaman sehingga tidak bisa dihapus permanen.');
        }

        if ($asset->photo) {
            Storage::disk('public')->delete($asset->photo);
        }

        $asset->forceDelete();

        return back()->with('success', 'Aset berhasil dihapus permanen.');
    }

    public function edit(Asset $asset)
    {
        $categories = Category::orderBy('name')->get();

        return view('assets.edit', compact('asset', 'categories'));
    }

    public function update(AssetRequest $request, Asset $asset)
    {
        $data = $request->validated();
        unset($data['photo']);

        // Status aset yang sedang dipinjam tidak boleh diubah manual
        if ($asset->status === 'dipinjam') {
            unset($data['status']);
        }

        if ($request->hasFile('photo')) {
            if ($asset->photo) {
                Storage::disk('public')->delete($asset->photo);
            }
            $data['photo'] = $request->file('photo')->store('assets', 'public');
        }

        $asset->update($data);

        return redirect()->route('assets.index')
            ->with('success', 'Aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->status === 'dipinjam') {
            return back()->with('error', 'Aset sedang dipinjam, tidak bisa dihapus.');
        }

        $asset->delete(); // soft delete, foto sengaja dipertahankan

        return redirect()->route('assets.index')
            ->with('success', 'Aset berhasil dihapus.');
    }
}
