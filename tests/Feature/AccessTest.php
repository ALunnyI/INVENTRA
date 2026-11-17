<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite(); // view tidak perlu aset hasil build Vite
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function staff(): User
    {
        return User::factory()->create(['role' => 'staff']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/assets')->assertRedirect('/login');
    }

    public function test_staff_cannot_open_master_data_pages(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->get('/categories')->assertForbidden();
        $this->actingAs($staff)->get('/departments')->assertForbidden();
        $this->actingAs($staff)->get('/assets/create')->assertForbidden();
    }

    public function test_admin_can_open_master_data_pages(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/categories')->assertOk();
        $this->actingAs($admin)->get('/departments')->assertOk();
        $this->actingAs($admin)->get('/assets/create')->assertOk();
    }

    public function test_staff_can_view_asset_list(): void
    {
        $this->actingAs($this->staff())->get('/assets')->assertOk();
    }

    public function test_dashboard_renders_for_admin_and_staff_with_empty_data(): void
    {
        $this->actingAs($this->admin())->get('/dashboard')->assertOk();
        $this->actingAs($this->staff())->get('/dashboard')->assertOk();
    }

    public function test_category_in_use_cannot_be_deleted(): void
    {
        $category = Category::create(['name' => 'Laptop']);
        \App\Models\Asset::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
