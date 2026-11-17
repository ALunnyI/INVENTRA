<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetTrashTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function deletedAsset(): Asset
    {
        $category = Category::firstOrCreate(['name' => 'Laptop']);
        $asset = Asset::factory()->create(['category_id' => $category->id, 'status' => 'tersedia']);
        $asset->delete();

        return $asset;
    }

    public function test_admin_can_see_and_restore_a_deleted_asset(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $asset = $this->deletedAsset();

        $this->actingAs($admin)->get(route('assets.trashed'))
            ->assertOk()
            ->assertSee($asset->asset_code);

        $this->actingAs($admin)->patch(route('assets.restore', $asset))
            ->assertRedirect(route('assets.index'));

        $this->assertNull($asset->fresh()->deleted_at);
    }

    public function test_staff_cannot_access_trash_or_restore(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $asset = $this->deletedAsset();

        $this->actingAs($staff)->get(route('assets.trashed'))->assertForbidden();
        $this->actingAs($staff)->patch(route('assets.restore', $asset))->assertForbidden();
        $this->actingAs($staff)->get(route('assets.show', $asset))->assertNotFound();
    }

    public function test_asset_with_loan_history_cannot_be_permanently_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $asset = $this->deletedAsset();

        Loan::create([
            'asset_id'    => $asset->id,
            'user_id'     => $admin->id,
            'loan_date'   => today()->subDays(10),
            'due_date'    => today()->subDays(3),
            'returned_at' => now()->subDays(4),
        ]);

        $this->actingAs($admin)->delete(route('assets.force-destroy', $asset))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('assets', ['id' => $asset->id]);
    }

    public function test_asset_without_history_can_be_permanently_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $asset = $this->deletedAsset();

        $this->actingAs($admin)->delete(route('assets.force-destroy', $asset))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }
}
