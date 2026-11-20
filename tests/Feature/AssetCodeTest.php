<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function payload(Category $category, array $extra = []): array
    {
        return array_merge([
            'category_id' => $category->id,
            'name'        => 'Laptop Uji',
            'condition'   => 'baik',
        ], $extra);
    }

    public function test_code_is_generated_from_category_prefix_and_increments(): void
    {
        $admin    = $this->admin();
        $category = Category::create(['name' => 'Laptop', 'code_prefix' => 'LPT']);

        $this->actingAs($admin)->post(route('assets.store'), $this->payload($category))
            ->assertRedirect(route('assets.index'));
        $this->actingAs($admin)->post(route('assets.store'), $this->payload($category));

        $this->assertDatabaseHas('assets', ['asset_code' => 'LPT-001']);
        $this->assertDatabaseHas('assets', ['asset_code' => 'LPT-002']);
    }

    public function test_numbering_never_reuses_a_deleted_assets_code(): void
    {
        $category = Category::create(['name' => 'Laptop', 'code_prefix' => 'LPT']);
        Asset::factory()->create(['category_id' => $category->id, 'asset_code' => 'LPT-005'])->delete();

        $this->actingAs($this->admin())->post(route('assets.store'), $this->payload($category));

        $this->assertDatabaseHas('assets', ['asset_code' => 'LPT-006']);
    }

    public function test_category_without_prefix_falls_back_to_default(): void
    {
        $category = Category::create(['name' => 'Lain-lain']);

        $this->actingAs($this->admin())->post(route('assets.store'), $this->payload($category));

        $this->assertDatabaseHas('assets', ['asset_code' => 'AST-001']);
    }

    public function test_manual_code_is_still_accepted_but_duplicates_are_rejected(): void
    {
        $admin    = $this->admin();
        $category = Category::create(['name' => 'Laptop', 'code_prefix' => 'LPT']);

        $this->actingAs($admin)->post(route('assets.store'), $this->payload($category, ['asset_code' => '3.2.102']));
        $this->assertDatabaseHas('assets', ['asset_code' => '3.2.102']);

        $this->actingAs($admin)->post(route('assets.store'), $this->payload($category, ['asset_code' => '3.2.102']))
            ->assertSessionHasErrors('asset_code');
    }

    public function test_code_is_required_when_editing(): void
    {
        $category = Category::create(['name' => 'Laptop', 'code_prefix' => 'LPT']);
        $asset    = Asset::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->put(route('assets.update', $asset), $this->payload($category, ['asset_code' => '']))
            ->assertSessionHasErrors('asset_code');
    }

    public function test_preview_endpoint_is_admin_only(): void
    {
        $category = Category::create(['name' => 'Laptop', 'code_prefix' => 'LPT']);

        $this->actingAs($this->admin())
            ->get(route('assets.next-code', ['category_id' => $category->id]))
            ->assertOk()
            ->assertJson(['code' => 'LPT-001']);

        $this->actingAs(User::factory()->create(['role' => 'staff']))
            ->get(route('assets.next-code', ['category_id' => $category->id]))
            ->assertForbidden();
    }

    public function test_category_prefix_must_be_unique_and_well_formed(): void
    {
        $admin = $this->admin();
        Category::create(['name' => 'Laptop', 'code_prefix' => 'LPT']);

        $this->actingAs($admin)->post(route('categories.store'), ['name' => 'Netbook', 'code_prefix' => 'lpt'])
            ->assertSessionHasErrors('code_prefix');
        $this->actingAs($admin)->post(route('categories.store'), ['name' => 'Tablet', 'code_prefix' => 'T@B!'])
            ->assertSessionHasErrors('code_prefix');
    }
}
