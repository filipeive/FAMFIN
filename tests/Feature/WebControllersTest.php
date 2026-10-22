<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\DebtType;
use App\Enums\FinancialRuleType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Debt;
use App\Models\Family;
use App\Models\FinancialRule;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebControllersTest extends TestCase
{
    use RefreshDatabase;

    protected Family $family;
    protected Account $account;
    protected Category $category;
    protected \App\Models\User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $this->user = \App\Models\User::factory()->create([
            'family_id' => $this->family->id,
            'name' => 'Filipe dos Santos',
            'email' => 'filipe.test@famfin.app',
        ]);
        $this->actingAs($this->user);

        $this->account = Account::create([
            'family_id' => $this->family->id,
            'name' => 'Millennium BIM Filipe',
            'type' => AccountType::Checking,
            'currency' => 'MZN',
            'initial_balance' => 100000,
            'balance' => 100000,
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'family_id' => $this->family->id,
            'name' => 'Alimentação & Supermercado',
            'type' => 'expense',
            'icon' => 'shopping-cart',
            'color' => '#10B981',
            'sort_order' => 1,
        ]);
    }

    public function test_dashboard_renders_successfully(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Visão Geral Financeira');
        $response->assertSee('Património Líquido');
    }

    public function test_transactions_index_and_create(): void
    {
        Transaction::create([
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => TransactionType::Expense,
            'amount' => 50000,
            'description' => 'Compras Supermercado',
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->get(route('transactions.index'));
        $response->assertStatus(200);
        $response->assertSee('Compras Supermercado');
        $response->assertSee('Transações');

        $createResponse = $this->get(route('transactions.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Registar Nova Transação');
    }

    public function test_transaction_store_and_destroy(): void
    {
        $response = $this->post(route('transactions.store'), [
            'account_id' => $this->account->id,
            'type' => 'income',
            'amount' => '25.000,00',
            'description' => 'Consultoria Extra',
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'description' => 'Consultoria Extra',
            'amount' => 2500000,
        ]);

        $tx = Transaction::where('description', 'Consultoria Extra')->first();
        $deleteResponse = $this->delete(route('transactions.destroy', $tx));
        $deleteResponse->assertRedirect();
        $this->assertSoftDeleted('transactions', ['id' => $tx->id]);
    }

    public function test_transaction_store_auto_categorizes_salary_and_creates_deductions(): void
    {
        // Seed Salary category
        $salaryCategory = Category::create([
            'family_id' => $this->family->id,
            'name' => 'Salário',
            'type' => 'income',
        ]);

        $response = $this->post(route('transactions.store'), [
            'account_id' => $this->account->id,
            'type' => 'income',
            'amount' => '80.000,00',
            'description' => 'Salário Life Child Setembro',
            'transaction_date' => now()->toDateString(),
            'deduct_tithe' => '1',
            'deduct_inss' => '1',
        ]);

        $response->assertRedirect(route('transactions.index'));

        // Verify parent income was auto-categorized to Salário
        $income = Transaction::where('description', 'Salário Life Child Setembro')->first();
        $this->assertNotNull($income);
        $this->assertEquals($salaryCategory->id, $income->category_id);
        $this->assertEquals(8000000, $income->amount); // 80.000,00 MZN

        // Verify INSS child (3% = 2.400,00 MZN = 240000 centavos)
        $this->assertDatabaseHas('transactions', [
            'parent_transaction_id' => $income->id,
            'amount' => 240000,
            'type' => 'expense',
        ]);

        // Verify Tithe child (10% = 8.000,00 MZN = 800000 centavos)
        $this->assertDatabaseHas('transactions', [
            'parent_transaction_id' => $income->id,
            'amount' => 800000,
            'type' => 'expense',
        ]);
    }

    public function test_budgets_controller_index_and_store(): void
    {
        $response = $this->get(route('budgets.index', ['month' => '2026-09']));
        $response->assertStatus(200);
        $response->assertSee('Gestão de Orçamentos Mensais');

        $storeResponse = $this->post(route('budgets.store'), [
            'category_id' => $this->category->id,
            'month' => '2026-09',
            'amount' => '15.000,00',
            'alert_threshold' => 80,
        ]);

        $storeResponse->assertRedirect(route('budgets.index', ['month' => '2026-09']));
        $this->assertDatabaseHas('budgets', [
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'month' => '2026-09',
            'amount' => 1500000,
        ]);

        // Copy budgets
        $copyResponse = $this->post(route('budgets.copy'), [
            'from_month' => '2026-09',
            'to_month' => '2026-10',
        ]);

        $copyResponse->assertRedirect(route('budgets.index', ['month' => '2026-10']));
        $this->assertDatabaseHas('budgets', [
            'month' => '2026-10',
            'amount' => 1500000,
        ]);
    }

    public function test_savings_controller_and_contributions(): void
    {
        $response = $this->get(route('savings.index'));
        $response->assertStatus(200);
        $response->assertSee('Metas de Poupança');

        $storeResponse = $this->post(route('savings.store'), [
            'name' => 'Reserva de Emergência',
            'target_amount' => '100.000,00',
            'deadline' => '2026-12-31',
        ]);

        $storeResponse->assertRedirect(route('savings.index'));
        $this->assertDatabaseHas('savings_goals', [
            'name' => 'Reserva de Emergência',
            'target_amount' => 10000000,
        ]);

        $goal = SavingsGoal::where('name', 'Reserva de Emergência')->first();

        $contribResponse = $this->post(route('savings.contribute', $goal), [
            'amount' => '10.000,00',
            'account_id' => $this->account->id,
            'note' => 'Aporte inicial',
        ]);

        $contribResponse->assertRedirect(route('savings.index'));
        $goal->refresh();
        $this->assertEquals(1000000, $goal->current_amount);
    }

    public function test_debts_controller_and_payments(): void
    {
        $response = $this->get(route('debts.index'));
        $response->assertStatus(200);
        $response->assertSee('Dívidas, Empréstimos & Financiamentos', false);

        $storeResponse = $this->post(route('debts.store'), [
            'creditor' => 'Millennium BIM',
            'description' => 'Crédito Pessoal',
            'type' => 'borrowed',
            'original_amount' => '50.000,00',
            'monthly_payment' => '5.000,00',
        ]);

        $storeResponse->assertRedirect(route('debts.index'));
        $this->assertDatabaseHas('debts', [
            'creditor' => 'Millennium BIM',
            'original_amount' => 5000000,
        ]);

        $debt = Debt::where('creditor', 'Millennium BIM')->first();

        $payResponse = $this->post(route('debts.payment', $debt), [
            'amount' => '10.000,00',
            'account_id' => $this->account->id,
            'note' => 'Pagamento Parcela 1',
        ]);

        $payResponse->assertRedirect(route('debts.index'));
        $debt->refresh();
        $this->assertEquals(4000000, $debt->remaining_amount);
    }

    public function test_accounts_and_rules_controllers(): void
    {
        $accResponse = $this->get(route('accounts.index'));
        $accResponse->assertStatus(200);
        $accResponse->assertSee('Contas & Carteiras', false);

        $createAccResponse = $this->post(route('accounts.store'), [
            'name' => 'Vodacom M-Pesa',
            'type' => 'mobile_money',
            'initial_balance' => '5.000,00',
            'include_in_total' => '1',
        ]);

        $createAccResponse->assertRedirect(route('accounts.index'));
        $this->assertDatabaseHas('accounts', [
            'name' => 'Vodacom M-Pesa',
            'balance' => 500000,
        ]);

        // Rules
        $rulesResponse = $this->get(route('rules.index', ['simulate_gross' => '50.000,00']));
        $rulesResponse->assertStatus(200);
        $rulesResponse->assertSee('Regras Financeiras & Simulador de Deduções', false);

        $storeRuleResponse = $this->post(route('rules.store'), [
            'name' => 'INSS Moçambique',
            'type' => 'inss',
            'percentage' => '3.0',
            'apply_on_income' => '1',
            'base' => 'gross',
        ]);

        $storeRuleResponse->assertRedirect(route('rules.index'));
        $this->assertDatabaseHas('financial_rules', [
            'name' => 'INSS Moçambique',
            'percentage' => 3.0,
        ]);

        $rule = FinancialRule::where('name', 'INSS Moçambique')->first();
        $toggleResponse = $this->patch(route('rules.toggle', $rule));
        $toggleResponse->assertRedirect(route('rules.index'));
        $rule->refresh();
        $this->assertFalse($rule->is_active);
    }
}
