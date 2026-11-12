<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    public function definition(): array
    {
        // Kondisi baik lebih sering muncul daripada rusak
        $condition = fake()->randomElement([
            'baik', 'baik', 'baik', 'baik', 'rusak_ringan', 'rusak_berat',
        ]);

        // Aset rusak berat otomatis masuk maintenance.
        // Status "dipinjam" sengaja tidak dibuat di sini: itu urusan modul peminjaman (Tahap 9).
        $status = $condition === 'rusak_berat'
            ? 'maintenance'
            : fake()->randomElement(['tersedia', 'tersedia', 'tersedia', 'maintenance']);

        return [
            'category_id'   => Category::inRandomOrder()->value('id'),
            'asset_code'    => 'AST-' . fake()->unique()->numerify('####'),
            'name'          => fake()->words(2, true),
            'brand'         => fake()->company(),
            'serial_number' => strtoupper(fake()->bothify('??#####??')),
            'purchase_date' => fake()->dateTimeBetween('-4 years', '-1 month'),
            'condition'     => $condition,
            'status'        => $status,
            'photo'         => null,
            'notes'         => fake()->optional(0.3)->sentence(),
        ];
    }
}
