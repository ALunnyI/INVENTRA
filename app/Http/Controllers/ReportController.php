<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use App\Support\XlsxExport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index', [
            'categories' => Category::orderBy('name')->get(),
            'users'      => User::orderBy('name')->get(),
        ]);
    }

    /* ---------------------------- Laporan Aset ---------------------------- */

    public function assets(Request $request)
    {
        $this->validateAssetFilters($request);

        $assets = $this->assetQuery($request)->get();

        return view('reports.assets', [
            'assets'      => $assets,
            'byStatus'    => $assets->countBy('status'),
            'byCondition' => $assets->countBy('condition'),
            'filters'     => $this->assetFilterLabels($request),
        ]);
    }

    public function assetsXlsx(Request $request)
    {
        $this->validateAssetFilters($request);

        $rows = $this->assetQuery($request)->get()->map(fn (Asset $asset, int $i) => [
            $i + 1,
            $asset->asset_code,
            $asset->name,
            $asset->category->name,
            $asset->brand,
            $asset->serial_number,
            $asset->purchase_date, // Carbon, menjadi sel tanggal
            Asset::CONDITIONS[$asset->condition],
            Asset::STATUSES[$asset->status],
            $asset->notes,
        ]);

        return XlsxExport::download(
            filename: 'laporan-aset-' . now()->format('Ymd-His') . '.xlsx',
            sheetTitle: 'Status Aset',
            headings: ['No', 'Kode Aset', 'Nama', 'Kategori', 'Merek', 'No. Seri', 'Tgl. Pembelian', 'Kondisi', 'Status', 'Catatan'],
            rows: $rows,
            widths: [5, 14, 32, 20, 18, 20, 16, 14, 14, 40],
            dateColumns: [7],
        );
    }

    private function validateAssetFilters(Request $request): void
    {
        $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'status'      => ['nullable', Rule::in(array_keys(Asset::STATUSES))],
            'condition'   => ['nullable', Rule::in(array_keys(Asset::CONDITIONS))],
        ]);
    }

    private function assetQuery(Request $request)
    {
        return Asset::with('category')
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->status, fn ($q, $value) => $q->where('status', $value))
            ->when($request->condition, fn ($q, $value) => $q->where('condition', $value))
            ->orderBy('asset_code');
    }

    private function assetFilterLabels(Request $request): array
    {
        return array_values(array_filter([
            $request->category_id ? 'Kategori: ' . Category::find($request->category_id)?->name : null,
            $request->status ? 'Status: ' . Asset::STATUSES[$request->status] : null,
            $request->condition ? 'Kondisi: ' . Asset::CONDITIONS[$request->condition] : null,
        ]));
    }

    /* -------------------------- Laporan Peminjaman ------------------------- */

    public function loans(Request $request)
    {
        $this->validateLoanFilters($request);

        $loans = $this->loanQuery($request)->get();

        return view('reports.loans', [
            'loans'    => $loans,
            'byStatus' => $loans->countBy(fn (Loan $loan) => $loan->status),
            'filters'  => $this->loanFilterLabels($request),
        ]);
    }

    public function loansXlsx(Request $request)
    {
        $this->validateLoanFilters($request);

        $rows = $this->loanQuery($request)->get()->map(fn (Loan $loan, int $i) => [
            $i + 1,
            $loan->asset->asset_code,
            $loan->asset->name,
            $loan->user->name,
            $loan->user->department?->name,
            $loan->loan_date,
            $loan->due_date,
            $loan->returned_at,
            Loan::STATUS_LABELS[$loan->status],
            $loan->notes,
        ]);

        return XlsxExport::download(
            filename: 'laporan-peminjaman-' . now()->format('Ymd-His') . '.xlsx',
            sheetTitle: 'Peminjaman',
            headings: ['No', 'Kode Aset', 'Nama Aset', 'Peminjam', 'Departemen', 'Tgl. Pinjam', 'Jatuh Tempo', 'Tgl. Kembali', 'Status', 'Catatan'],
            rows: $rows,
            widths: [5, 14, 32, 22, 22, 14, 14, 14, 14, 40],
            dateColumns: [6, 7, 8],
        );
    }

    private function validateLoanFilters(Request $request): void
    {
        $request->validate([
            'from'    => ['nullable', 'date'],
            'to'      => ['nullable', 'date', 'after_or_equal:from'],
            'status'  => ['nullable', Rule::in(array_keys(Loan::STATUS_LABELS))],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);
    }

    private function loanQuery(Request $request)
    {
        return Loan::with(['asset', 'user.department'])
            ->when($request->from, fn ($q, $date) => $q->whereDate('loan_date', '>=', $date))
            ->when($request->to, fn ($q, $date) => $q->whereDate('loan_date', '<=', $date))
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->status, function ($q, $status) {
                match ($status) {
                    'dikembalikan' => $q->whereNotNull('returned_at'),
                    'terlambat'    => $q->overdue(),
                    'dipinjam'     => $q->active()->whereDate('due_date', '>=', today()),
                    default        => null,
                };
            })
            ->orderBy('loan_date')
            ->orderBy('id');
    }

    private function loanFilterLabels(Request $request): array
    {
        $period = null;

        if ($request->from || $request->to) {
            $format = fn ($date) => \Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
            $period = 'Periode: '
                . ($request->from ? $format($request->from) : 'awal')
                . ' s.d. '
                . ($request->to ? $format($request->to) : 'sekarang');
        }

        return array_values(array_filter([
            $period,
            $request->status ? 'Status: ' . Loan::STATUS_LABELS[$request->status] : null,
            $request->user_id ? 'Peminjam: ' . User::find($request->user_id)?->name : null,
        ]));
    }
}
