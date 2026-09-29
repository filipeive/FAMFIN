<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BudgetService $budgetService;
    protected Family $family;
    protected Category $category;
    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->budgetService = app(BudgetService::class);

        $this->family = Family::create([
            'name' => 'Família Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
        ]);

        $this->account = Account::create([
            'family_id' => $this->family->id,
            'name' => 'Conta Principal',
            'type' => AccountType::Checking,
            'initial_balance' => 5000000,
            'balance' => 5000000,
        ]);

        $this->category = Category::create([
            'family_id' => $this->family->id,
            'name' => 'Rancho',
            'type' => CategoryType::Expense,
        ]);
    }

    public function test_budget_status_ok_when_below_threshold(): void
    {
        // Set budget 5.000,00 MZN for 2026-09
        $this->budgetService->setBudget($this->family, $this->category->id, '2026-09', 500000, 80);

        // Expense 2.000,00 MZN (40%)
        Transaction::create([
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 200000,
            'description' => 'Compras semanais',
            'transaction_date' => '2026-09-10',
        ]);

        $status = $this->budgetService->getCategoryBudgetStatus($this->family, $this->category->id, '2026-09');

        $this->assertEquals('ok', $status['status']);
        $this->assertEquals(500000, $status['budget_amount']);
        $this->assertEquals(200000, $status['spent_amount']);
        $this->assertEquals(300000, $status['remaining_amount']);
        $this->assertEquals(40.0, $status['percentage_used']);
    }

    public function test_budget_status_warning_at_or_above_threshold(): void
    {
        $this->budgetService->setBudget($this->family, $this->category->id, '2026-09', 500000, 80);

        // Expense 4.200,00 MZN (84% >= 80%)
        Transaction::create([
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 420000,
            'description' => 'Rancho do mês',
            'transaction_date' => '2026-09-15',
        ]);

        $status = $this->budgetService->getCategoryBudgetStatus($this->family, $this->category->id, '2026-09');
        $this->assertEquals('warning', $status['status']);
        $this->assertEquals(84.0, $status['percentage_used']);
    }

    public function test_budget_status_exceeded_when_over_100_percent(): void
    {
        $this->budgetService->setBudget($this->family, $this->category->id, '2026-09', 500000, 80);

        // Expense 5.500,00 MZN (110%)
        Transaction::create([
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 550000,
            'description' => 'Gastos extras',
            'transaction_date' => '2026-09-20',
        ]);

        $status = $this->budgetService->getCategoryBudgetStatus($this->family, $this->category->id, '2026-09');
        $this->assertEquals('exceeded', $status['status']);
        $this->assertEquals(-50000, $status['remaining_amount']);
        $this->assertEquals(110.0, $status['percentage_used']);
    }

    public function test_copy_budgets_to_next_month(): void
    {
        $this->budgetService->setBudget($this->family, $this->category->id, '2026-09', 500000, 80);

        $copied = $this->budgetService->copyBudgetsToNextMonth($this->family, '2026-09', '2026-10');

        $this->assertEquals(1, $copied);
        $status = $this->budgetService->getCategoryBudgetStatus($this->family, $this->category->id, '2026-10');
        $this->assertEquals(500000, $status['budget_amount']);
    }
}
