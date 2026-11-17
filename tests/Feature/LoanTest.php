<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function makeAsset(array $attributes = []): Asset
    {
        $category = Category::firstOrCreate(['name' => 'Laptop']);

        return Asset::factory()->create(array_merge([
            'category_id' => $category->id,
            'status'      => 'tersedia',
            'condition'   => 'baik',
        ], $attributes));
    }

    private function borrowPayload(Asset $asset): array
    {
        return [
            'asset_id' => $asset->id,
            'due_date' => now()->addDays(7)->toDateString(),
        ];
    }

    private function activeLoanFor(User $user, Asset $asset): Loan
    {
        return Loan::create([
            'asset_id'  => $asset->id,
            'user_id'   => $user->id,
            'loan_date' => today(),
            'due_date'  => today()->addDays(7),
        ]);
    }

    public function test_staff_can_borrow_an_available_asset(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $asset = $this->makeAsset();

        $this->actingAs($staff)
            ->post(route('loans.store'), $this->borrowPayload($asset))
            ->assertRedirect(route('loans.index'));

        $this->assertDatabaseHas('loans', [
            'asset_id'    => $asset->id,
            'user_id'     => $staff->id,
            'returned_at' => null,
        ]);
        $this->assertSame('dipinjam', $asset->fresh()->status);
    }

    public function test_borrowed_asset_cannot_be_borrowed_again(): void
    {
        $asset = $this->makeAsset(['status' => 'dipinjam']);

        $this->actingAs(User::factory()->create(['role' => 'staff']))
            ->post(route('loans.store'), $this->borrowPayload($asset))
            ->assertSessionHasErrors('asset_id');

        $this->assertDatabaseCount('loans', 0);
    }

    public function test_due_date_beyond_maximum_is_rejected(): void
    {
        $asset = $this->makeAsset();

        $this->actingAs(User::factory()->create(['role' => 'staff']))
            ->post(route('loans.store'), [
                'asset_id' => $asset->id,
                'due_date' => now()->addDays(Loan::MAX_DURATION_DAYS + 5)->toDateString(),
            ])
            ->assertSessionHasErrors('due_date');
    }

    public function test_user_cannot_exceed_active_loan_limit(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        for ($i = 0; $i < Loan::MAX_ACTIVE_PER_USER; $i++) {
            $this->actingAs($staff)
                ->post(route('loans.store'), $this->borrowPayload($this->makeAsset()))
                ->assertRedirect(route('loans.index'));
        }

        $this->actingAs($staff)
            ->post(route('loans.store'), $this->borrowPayload($this->makeAsset()))
            ->assertSessionHasErrors('asset_id');

        $this->assertSame(Loan::MAX_ACTIVE_PER_USER, Loan::count());
    }

    public function test_admin_return_makes_asset_available_again(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $asset = $this->makeAsset(['status' => 'dipinjam']);
        $loan  = $this->activeLoanFor($staff, $asset);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch(route('loans.return', $loan), ['condition' => 'baik'])
            ->assertSessionHas('success');

        $this->assertNotNull($loan->fresh()->returned_at);
        $this->assertSame('tersedia', $asset->fresh()->status);
    }

    public function test_heavily_damaged_asset_goes_to_maintenance_on_return(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $asset = $this->makeAsset(['status' => 'dipinjam']);
        $loan  = $this->activeLoanFor($staff, $asset);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch(route('loans.return', $loan), ['condition' => 'rusak_berat']);

        $fresh = $asset->fresh();
        $this->assertSame('maintenance', $fresh->status);
        $this->assertSame('rusak_berat', $fresh->condition);
    }

    public function test_staff_cannot_record_a_return(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $asset = $this->makeAsset(['status' => 'dipinjam']);
        $loan  = $this->activeLoanFor($staff, $asset);

        $this->actingAs($staff)
            ->patch(route('loans.return', $loan), ['condition' => 'baik'])
            ->assertForbidden();

        $this->assertNull($loan->fresh()->returned_at);
    }

    public function test_staff_only_sees_own_loans(): void
    {
        $mine  = User::factory()->create(['role' => 'staff']);
        $other = User::factory()->create(['role' => 'staff']);

        $this->activeLoanFor($mine, $this->makeAsset(['name' => 'Laptop Milik Saya']));
        $this->activeLoanFor($other, $this->makeAsset(['name' => 'Laptop Orang Lain']));

        $this->actingAs($mine)->get(route('loans.index'))
            ->assertOk()
            ->assertSee('Laptop Milik Saya')
            ->assertDontSee('Laptop Orang Lain');
    }
}
