<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Teknologi Informasi',
            'Keuangan',
            'Sumber Daya Manusia',
            'Pemasaran',
            'Operasional',
            'Hukum dan Kepatuhan',
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
