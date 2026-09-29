<?php

namespace Tests\Feature;

use App\Enums\FinancialRuleType;
use App\Models\Family;
use App\Models\FinancialRule;
use App\Services\FinancialRuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialRuleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FinancialRuleService $service;
    protected Family $family;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FinancialRuleService::class);

        $this->family = Family::create([
            'name' => 'Família Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
        ]);
    }

    public function test_calculates_mozambique_inss_and_tithe_correctly(): void
    {
        // INSS 3%
        FinancialRule::create([
            'family_id' => $this->family->id,
            'type' => FinancialRuleType::Inss,
            'name' => 'INSS',
            'percentage' => 3.00,
            'is_active' => true,
            'apply_on_income' => true,
            'sort_order' => 1,
        ]);

        // Tithe 10%
        FinancialRule::create([
            'family_id' => $this->family->id,
            'type' => FinancialRuleType::Tithe,
            'name' => 'Dízimo',
            'percentage' => 10.00,
            'is_active' => true,
            'apply_on_income' => true,
            'config' => ['base' => 'gross'],
            'sort_order' => 2,
        ]);

        // Gross salary: 53.418,75 MZN = 5341875 centavos
        $gross = 5341875;
        $breakdown = $this->service->calculateBreakdown($this->family, $gross);

        // INSS = 5341875 * 0.03 = 160256 centavos (1.602,56 MZN)
        $inssDeduction = collect($breakdown['deductions'])->firstWhere('type', 'inss');
        $this->assertNotNull($inssDeduction);
        $this->assertEquals(160256, $inssDeduction['amount']);

        // Tithe = 5341875 * 0.10 = 534187 centavos (5.341,87 MZN)
        $titheDeduction = collect($breakdown['deductions'])->firstWhere('type', 'tithe');
        $this->assertNotNull($titheDeduction);
        $this->assertEquals(534187, $titheDeduction['amount']);

        // Total deductions = 160256 + 534187 = 694443 centavos (6.944,43 MZN)
        $this->assertEquals(694443, $breakdown['total_deductions']);

        // Net income = 5341875 - 694443 = 4647432 centavos (46.474,32 MZN)
        $this->assertEquals(4647432, $breakdown['net_amount']);
    }

    public function test_inactive_rules_are_not_applied(): void
    {
        FinancialRule::create([
            'family_id' => $this->family->id,
            'type' => FinancialRuleType::Inss,
            'name' => 'INSS',
            'percentage' => 3.00,
            'is_active' => false,
            'apply_on_income' => true,
        ]);

        $breakdown = $this->service->calculateBreakdown($this->family, 5000000);

        $this->assertEquals(0, $breakdown['total_deductions']);
        $this->assertEquals(5000000, $breakdown['net_amount']);
    }

    public function test_yield_calculation(): void
    {
        // 100.000,00 MZN at 12% annual rate for 30 days
        $balance = 10000000;
        $yield = $this->service->calculateYield($balance, 12.0, 30);

        // Daily rate: (0.12 / 365) * 30 = ~0.009863 => 10000000 * 0.009863 = 98630 centavos (986,30 MZN)
        $this->assertGreaterThan(90000, $yield);
        $this->assertLessThan(100000, $yield);
    }
}
