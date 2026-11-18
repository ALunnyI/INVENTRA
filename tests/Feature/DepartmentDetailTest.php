<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Department;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function loanFor(User $user, string $assetName, int $dueInDays): Loan
    {
        $category = Category::firstOrCreate(['name' => 'Laptop']);
        $asset = Asset::factory()->create([
            'category_id' => $category->id,
            'name'        => $assetName,
            'status'      => 'dipinjam',
        ]);

        return Loan::create([
            'asset_id'  => $asset->id,
            'user_id'   => $user->id,
            'loan_date' => today()->subDays(10),
            'due_date'  => today()->addDays($dueInDays),
        ]);
    }

    public function test_staff_cannot_open_department_detail(): void
    {
        $department = Department::create(['name' => 'Keuangan']);

        $this->actingAs(User::factory()->create(['role' => 'staff']))
            ->get(route('departments.show', $department))
            ->assertForbidden();
    }

    public function test_page_shows_members_contacts_borrowed_assets_and_reminder_link(): void
    {
        $department = Department::create(['name' => 'Keuangan']);
        $late = User::factory()->create([
            'role' => 'staff', 'department_id' => $department->id, 'phone' => '0812-3456-7890',
        ]);
        $this->loanFor($late, 'Laptop Terlambat', -3);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('departments.show', $department))
            ->assertOk()
            ->assertSee($late->name)
            ->assertSee($late->email)
            ->assertSee('Laptop Terlambat')
            ->assertSee('Terlambat 3 hari')
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('Ingatkan via WhatsApp');
    }

    public function test_reminder_is_only_offered_to_members_with_overdue_loans(): void
    {
        $department = Department::create(['name' => 'Keuangan']);
        $onTime = User::factory()->create(['role' => 'staff', 'department_id' => $department->id]);
        $this->loanFor($onTime, 'Laptop Aman', 5);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('departments.show', $department))
            ->assertOk()
            ->assertSee('Laptop Aman')
            ->assertDontSee('Ingatkan via');
    }

    public function test_whatsapp_number_is_normalised(): void
    {
        $this->assertSame('6281234567890', (new User(['phone' => '0812-3456-7890']))->whatsapp_number);
        $this->assertSame('6281234567890', (new User(['phone' => '+62 812 3456 7890']))->whatsapp_number);
        $this->assertSame('6281234567890', (new User(['phone' => '81234567890']))->whatsapp_number);
        $this->assertNull((new User(['phone' => null]))->whatsapp_number);
    }
}
