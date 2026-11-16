<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        if (Loan::exists()) {
            return;
        }

        $staff = User::where('role', 'staff')->get()->shuffle()->values();

        if ($staff->isEmpty()) {
            return;
        }

        $assets = Asset::where('status', 'tersedia')->inRandomOrder()->take(11)->get();
        $pick   = fn (int $i) => $staff[$i % $staff->count()];

        // 6 peminjaman yang sudah dikembalikan (riwayat)
        foreach ($assets->slice(0, 6)->values() as $i => $asset) {
            $loanDate = now()->subDays(rand(20, 60))->startOfDay();

            Loan::create([
                'asset_id'    => $asset->id,
                'user_id'     => $pick($i)->id,
                'loan_date'   => $loanDate,
                'due_date'    => $loanDate->copy()->addDays(7),
                'returned_at' => $loanDate->copy()->addDays(rand(1, 7)),
            ]);
        }

        // 3 peminjaman aktif
        foreach ($assets->slice(6, 3)->values() as $i => $asset) {
            Loan::create([
                'asset_id'  => $asset->id,
                'user_id'   => $pick($i)->id,
                'loan_date' => today()->subDays(rand(0, 3)),
                'due_date'  => today()->addDays(rand(3, 10)),
            ]);
            $asset->update(['status' => 'dipinjam']);
        }

        // 2 peminjaman terlambat
        foreach ($assets->slice(9, 2)->values() as $i => $asset) {
            Loan::create([
                'asset_id'  => $asset->id,
                'user_id'   => $pick($i + 3)->id,
                'loan_date' => today()->subDays(20),
                'due_date'  => today()->subDays(5),
            ]);
            $asset->update(['status' => 'dipinjam']);
        }
    }
}
