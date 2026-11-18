<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tiId = Department::where('name', 'Teknologi Informasi')->value('id');

        $admin = User::firstOrNew(['email' => 'admin@inventra.com']);
        $admin->forceFill([
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'department_id' => $tiId,
            'email_verified_at' => now(),
            'phone' => '081234567890',
        ])->save();

        $staff = User::firstOrNew(['email' => 'staff@inventra.com']);
        $staff->forceFill([
            'name' => 'Al',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'department_id' => Department::where('name', 'Keuangan')->value('id'),
            'email_verified_at' => now(),
            'phone' => '081298765432',
        ])->save();

        // 5 staff acak dari factory bawaan Laravel
        User::factory(5)->state(fn () => [
            'role' => 'staff',
            'department_id' => Department::inRandomOrder()->value('id'),
            'phone'         => fake()->numerify('08##########'),
        ])->create();
    }
}
