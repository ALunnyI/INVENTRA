<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Laptop' => 'Laptop kerja untuk karyawan dan peminjaman sementara.',
            'Monitor' => 'Monitor dan layar tambahan.',
            'Printer' => 'Printer dan perangkat cetak.',
            'Proyektor' => 'Proyektor untuk rapat dan presentasi.',
            'Perangkat Jaringan' => 'Router, switch, dan access point.',
            'Smartphone & Tablet' => 'Perangkat mobile untuk pengujian dan operasional.',
        ];

        foreach ($categories as $name => $description) {
            Category::firstOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
