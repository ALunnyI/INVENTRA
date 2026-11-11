<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssetRequest;
use App\Models\Asset;
use App\Models\Category;
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

        Asset::create($data);

        return redirect()->route('assets.index')
            ->with('success', 'Aset berhasil ditambahkan.');
    }

    public function show(Asset $asset)
    {
        $asset->load('category');

        return view('assets.show', compact('asset'));
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
