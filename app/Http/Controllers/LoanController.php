<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanRequest;
use App\Models\Asset;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('admin', only: ['markReturned'])];
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $loans = Loan::with(['asset', 'user'])
            // staff hanya melihat peminjamannya sendiri
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->search, function ($q, $search) {
                $q->whereHas('asset', function ($a) use ($search) {
                    $a->withTrashed()->where(function ($w) use ($search) {
                        $w->where('name', 'like', "%{$search}%")
                          ->orWhere('asset_code', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->status, function ($q, $status) {
                match ($status) {
                    'dikembalikan' => $q->whereNotNull('returned_at'),
                    'terlambat'    => $q->overdue(),
                    'dipinjam'     => $q->active()->whereDate('due_date', '>=', today()),
                    default        => null,
                };
            })
            ->latest('loan_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('loans.index', compact('loans'));
    }

    public function create(Request $request)
    {
        $assets = Asset::where('status', 'tersedia')->orderBy('name')->get();
        $users  = $request->user()->isAdmin() ? User::orderBy('name')->get() : collect();

        return view('loans.create', [
            'assets'        => $assets,
            'users'         => $users,
            'selectedAsset' => $request->query('asset_id'),
        ]);
    }

    public function store(StoreLoanRequest $request)
    {
        $data  = $request->validated();
        $actor = $request->user();

        // Staff selalu meminjam untuk dirinya sendiri; admin boleh memilih peminjam
        $borrowerId = ($actor->isAdmin() && ! empty($data['user_id']))
            ? (int) $data['user_id']
            : $actor->id;

        DB::transaction(function () use ($data, $borrowerId) {
            // Kunci baris aset supaya dua request bersamaan tidak lolos keduanya
            $asset = Asset::lockForUpdate()->findOrFail($data['asset_id']);

            if ($asset->status !== 'tersedia') {
                throw ValidationException::withMessages([
                    'asset_id' => 'Aset ini sedang tidak tersedia untuk dipinjam.',
                ]);
            }

            $activeCount = Loan::active()->where('user_id', $borrowerId)->count();

            if ($activeCount >= Loan::MAX_ACTIVE_PER_USER) {
                throw ValidationException::withMessages([
                    'asset_id' => 'Peminjam sudah mencapai batas ' . Loan::MAX_ACTIVE_PER_USER . ' peminjaman aktif.',
                ]);
            }

            Loan::create([
                'asset_id'  => $asset->id,
                'user_id'   => $borrowerId,
                'loan_date' => today(),
                'due_date'  => $data['due_date'],
                'notes'     => $data['notes'] ?? null,
            ]);

            $asset->update(['status' => 'dipinjam']);
        });

        return redirect()->route('loans.index')
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function markReturned(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'condition' => ['required', Rule::in(array_keys(Asset::CONDITIONS))],
        ]);

        $returned = DB::transaction(function () use ($loan, $data) {
            $fresh = Loan::lockForUpdate()->find($loan->id);

            // Cegah pengembalian ganda (misalnya tombol diklik di dua tab)
            if ($fresh->returned_at) {
                return false;
            }

            $fresh->update(['returned_at' => now()]);

            $asset = Asset::withTrashed()->lockForUpdate()->find($fresh->asset_id);
            $asset->update([
                'condition' => $data['condition'],
                'status'    => $data['condition'] === 'rusak_berat' ? 'maintenance' : 'tersedia',
            ]);

            return true;
        });

        if (! $returned) {
            return back()->with('error', 'Peminjaman ini sudah dicatat sebagai dikembalikan.');
        }

        return back()->with('success', 'Pengembalian berhasil dicatat.');
    }
}
