<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Loan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        // Satu query untuk semua hitungan status aset
        $assetsByStatus = Asset::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => $assetsByStatus->sum(),
            'tersedia' => $assetsByStatus->get('tersedia', 0),
            'dipinjam' => $assetsByStatus->get('dipinjam', 0),
            'maintenance' => $assetsByStatus->get('maintenance', 0),
        ];

        // Query peminjaman: admin melihat semua, staff hanya miliknya.
        // Dibuat sebagai fungsi supaya tiap pemanggilan menghasilkan query baru.
        $loans = fn () => Loan::query()
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id));

        $activeLoans = $loans()->active()->count();
        $overdueLoans = $loans()->overdue()->count();
        $dueSoon = $loans()->active()
            ->whereBetween('due_date', [today(), today()->addDays(3)])
            ->count();

        // Terlambat + jatuh tempo dalam 3 hari ke depan, yang paling mendesak di atas
        $attentionLoans = $loans()->active()
            ->whereDate('due_date', '<=', today()->addDays(3))
            ->with(['asset', 'user'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $recentLoans = $loans()
            ->with(['asset', 'user'])
            ->latest()
            ->limit(5)
            ->get();

        // Khusus admin
        $categories = collect();
        $conditionCounts = collect();

        if ($isAdmin) {
            $categories = Category::withCount('assets')
                ->orderByDesc('assets_count')
                ->get();

            $conditionCounts = Asset::selectRaw('`condition`, COUNT(*) as total')
                ->groupBy('condition')
                ->pluck('total', 'condition');
        }

        return view('dashboard', compact(
            'stats', 'activeLoans', 'overdueLoans', 'dueSoon',
            'attentionLoans', 'recentLoans', 'categories', 'conditionCounts'
        ));
    }
}
