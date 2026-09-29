<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\DebtType;
use App\Enums\FinancialRuleType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Debt;
use App\Models\Family;
use App\Models\FinancialRule;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebControllersTest extends TestCase
{
    use RefreshDatabase;

    protected Family $family;
    protected User $user;
    protected Account $account;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Família dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
        ]);

        $this->user = User::factory()->create([
            'family_id' => $this->family->id,
            'name' => 'Filipe dos Santos',
            'email' => 'filipe@famfin.app',
        ]);

        $this->account = Account::create([
            'family_id' => $this->family->id,
            'name' => 'Conta BCI',
            'type' => AccountType::Checking,
            'initial_balance' => 1000000, // 10.000,00 MZN
            'balance' => 1000000,
        ]);

        $this->category = Category::create([
            'family_id' => $this->family->id,
            'name' => 'Rancho',
            'type' => CategoryType::Expense,
        ]);

        FinancialRule::create([
            'family_id' => $this->family->id,
            'type' => FinancialRuleType::Inss,
            'name' => 'INSS',
            'percentage' => 3.00,
            'is_active' => true,
            'apply_on_income' => true,
        ]);
    }

    public function test_dashboard_renders_successfully(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Painel Financeiro');
        $response->assertSee('Família dos Santos');
        $response->assertSee('MZN');
    }

    public function test_transactions_index_and_create(): void
    {
        $response = $this->get(route('transactions.index'));
        $response->assertStatus(200);
        $response->assertSee('Transacções');

        $createResponse = $this->get(route('transactions.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Nova Transacção');
    }

    public function test_store_and_delete_transaction(): void
    {
        $storeResponse = $this->post(route('transactions.store'), [
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => '2500,00',
            'description' => 'Compras semanais',
            'transaction_date' => now()->toDateString(),
        ]);

        $storeResponse->assertRedirect(route('transactions.index'));
        $storeResponse->assertSessionHas('success');

        $this->account->refresh();
        $this->assertEquals(750000, $this->account->balance); // 10.000 - 2.500 = 7.500 MZN

        $transaction = Transaction::where('description', 'Compras semanais')->first();
        $this->assertNotNull($transaction);

        // Delete transaction
        $deleteResponse = $this->delete(route('transactions.destroy', $transaction));
        $deleteResponse->assertSessionHas('success');

        $this->account->refresh();
        $this->assertEquals(1000000, $this->account->balance); // balance restored
    }

    public function test_budgets_index_and_store(): void
    {
        $response = $this->get(route('budgets.index'));
        $response->assertStatus(200);
        $response->assertSee('Orçamentos Mensais');

        $storeResponse = $this->post(route('budgets.store'), [
            'category_id' => $this->category->id,
            'month' => '2026-09',
            'amount' => '5000,00',
            'alert_threshold' => 80,
        ]);

        $storeResponse->assertSessionHas('success');
        $this->assertDatabaseHas('budgets', [
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'month' => '2026-09',
            'amount' => 500000,
        ]);
    }

    public function test_savings_goals_and_contributions(): void
    {
        $response = $this->get(route('savings.index'));
        $response->assertStatus(200);
        $response->assertSee('Metas de Poupança');

        $storeResponse = $this->post(route('savings.store'), [
            'name' => 'Fundo de Emergência',
            'target_amount' => '50000,00',
        ]);
        $storeResponse->assertSessionHas('success');

        $goal = SavingsGoal::where('name', 'Fundo de Emergência')->first();
        $this->assertNotNull($goal);

        $contribResponse = $this->post(route('savings.contribute', $goal->id), [
            'amount' => '10000,00',
            'account_id' => $this->account->id,
        ]);
        $contribResponse->assertSessionHas('success');

        $goal->refresh();
        $this->assertEquals(1000000, $goal->current_amount);
    }

    public function test_debts_and_repayments(): void
    {
        $response = $this->get(route('debts.index'));
        $response->assertStatus(200);
        $response->assertSee('Gestão de Dívidas');

        $storeResponse = $this->post(route('debts.store'), [
            'person_name' => 'Laurinda',
            'type' => 'borrowed',
            'original_amount' => '10000,00',
        ]);
        $storeResponse->assertSessionHas('success');

        $debt = Debt::where('person_name', 'Laurinda')->first();
        $this->assertNotNull($debt);

        $payResponse = $this->post(route('debts.pay', $debt->id), [
            'amount' => '3000,00',
            'account_id' => $this->account->id,
        ]);
        $payResponse->assertSessionHas('success');

        $debt->refresh();
        $this->assertEquals(700000, $debt->remaining_amount);
    }

    public function test_accounts_and_rules_controllers(): void
    {
        $accResponse = $this->get(route('accounts.index'));
        $accResponse->assertStatus(200);
        $accResponse->assertSee('Contas');

        $rulesResponse = $this->get(route('rules.index'));
        $rulesResponse->assertStatus(200);
        $rulesResponse->assertSee('Regras');

        $rule = FinancialRule::first();
        $toggleResponse = $this->post(route('rules.toggle', $rule->id));
        $toggleResponse->assertSessionHas('success');

        $rule->refresh();
        $this->assertFalse($rule->is_active);
    }
}
