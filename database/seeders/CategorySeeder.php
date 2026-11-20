<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
{
    $categories = [
        'Laptop'              => ['LPT', 'Laptop kerja untuk karyawan dan peminjaman sementara.'],
        'Monitor'             => ['MON', 'Monitor dan layar tambahan.'],
        'Printer'             => ['PRN', 'Printer dan perangkat cetak.'],
        'Proyektor'           => ['PRJ', 'Proyektor untuk rapat dan presentasi.'],
        'Perangkat Jaringan'  => ['NET', 'Router, switch, dan access point.'],
        'Smartphone & Tablet' => ['MOB', 'Perangkat mobile untuk pengujian dan operasional.'],
    ];

    foreach ($categories as $name => [$prefix, $description]) {
        Category::updateOrCreate(
            ['name' => $name],
            ['code_prefix' => $prefix, 'description' => $description]
        );
    }
}
}
