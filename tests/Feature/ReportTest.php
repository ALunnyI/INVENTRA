<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
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

    private function makeAsset(array $attributes = []): Asset
    {
        $category = Category::firstOrCreate(['name' => 'Laptop']);

        return Asset::factory()->create(array_merge(['category_id' => $category->id], $attributes));
    }

    private function makeLoan(Asset $asset, User $user, string $loanDate): Loan
    {
        return Loan::create([
            'asset_id'  => $asset->id,
            'user_id'   => $user->id,
            'loan_date' => $loanDate,
            'due_date'  => now()->parse($loanDate)->addDays(7),
        ]);
    }

    public function test_staff_cannot_open_reports(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('reports.assets'))->assertForbidden();
        $this->actingAs($staff)->get(route('reports.loans.csv'))->assertForbidden();
    }

    public function test_asset_report_lists_assets_and_respects_filters(): void
    {
        $available   = $this->makeAsset(['name' => 'Laptop Tersedia', 'status' => 'tersedia']);
        $maintenance = $this->makeAsset(['name' => 'Laptop Servis', 'status' => 'maintenance']);

        $this->actingAs($this->admin())
            ->get(route('reports.assets', ['status' => 'maintenance']))
            ->assertOk()
            ->assertSee('Laporan Status Aset')
            ->assertSee($maintenance->name)
            ->assertDontSee($available->name);
    }

    public function test_loan_report_filters_by_period(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $inside  = $this->makeAsset(['name' => 'Aset Dalam Periode']);
        $outside = $this->makeAsset(['name' => 'Aset Luar Periode']);

        $this->makeLoan($inside, $staff, '2026-03-10');
        $this->makeLoan($outside, $staff, '2026-01-05');

        $this->actingAs($this->admin())
            ->get(route('reports.loans', ['from' => '2026-03-01', 'to' => '2026-03-31']))
            ->assertOk()
            ->assertSee('Aset Dalam Periode')
            ->assertDontSee('Aset Luar Periode');
    }

    public function test_loan_report_validates_date_range(): void
    {
        $this->actingAs($this->admin())
            ->get(route('reports.loans', ['from' => '2026-03-31', 'to' => '2026-03-01']))
            ->assertSessionHasErrors('to');
    }

    public function test_asset_csv_is_downloadable_and_neutralises_formulas(): void
    {
        $this->makeAsset(['asset_code' => 'CSV-001', 'name' => '=HYPERLINK("http://contoh.test")']);

        $response = $this->actingAs($this->admin())->get(route('reports.assets.csv'));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('CSV-001', $content);
        $this->assertStringContainsString("'=HYPERLINK", $content);
    }
}
