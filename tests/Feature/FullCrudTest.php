<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\DebtType;
use App\Enums\FinancialRuleType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Asset;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Debt;
use App\Models\Family;
use App\Models\FinancialRule;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Family $family;
    protected Account $account;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->family = Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $user = \App\Models\User::factory()->create(['family_id' => $this->family->id]);
        $this->actingAs($user);

        $this->account = Account::create([
            'family_id' => $this->family->id,
            'name' => 'Millennium BIM',
            'type' => AccountType::Checking,
            'currency' => 'MZN',
            'initial_balance' => 100000,
            'balance' => 100000,
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'family_id' => $this->family->id,
            'name' => 'Alimentação',
            'type' => CategoryType::Expense,
            'color' => '#10B981',
        ]);
    }

    public function test_transaction_edit_and_update_recalculates_balance(): void
    {
        $tx = Transaction::create([
            'account_id' => $this->account->id,
            'category_id' => $this->category->id,
            'type' => TransactionType::Expense,
            'amount' => 20000,
            'description' => 'Jantar',
            'transaction_date' => now()->toDateString(),
        ]);

        // Account balance decreased by 20000 (100000 - 20000 = 80000)
        $this->account->decrement('balance', 20000);

        // Edit screen renders
        $editResponse = $this->get(route('transactions.edit', $tx));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Editar Transação');

        // Update transaction amount to 30000
        $updateResponse = $this->put(route('transactions.update', $tx), [
            'account_id' => $this->account->id,
            'type' => 'expense',
            'amount' => '300,00',
            'description' => 'Jantar Executivo',
            'transaction_date' => now()->toDateString(),
        ]);

        $updateResponse->assertRedirect(route('transactions.index'));
        $tx->refresh();
        $this->assertEquals('Jantar Executivo', $tx->description);
        $this->assertEquals(30000, $tx->amount);
    }

    public function test_account_show_edit_update_destroy(): void
    {
        // Show
        $showResponse = $this->get(route('accounts.show', $this->account));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Millennium BIM');

        // Edit
        $editResponse = $this->get(route('accounts.edit', $this->account));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Editar Dados da Conta');

        // Update
        $updateResponse = $this->put(route('accounts.update', $this->account), [
            'name' => 'Millennium BIM Atualizada',
            'type' => 'checking',
            'bank_name' => 'BIM Mozambique',
            'account_number' => '123456789',
            'include_in_total' => '1',
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect(route('accounts.index'));
        $this->account->refresh();
        $this->assertEquals('Millennium BIM Atualizada', $this->account->name);

        // Delete without transactions
        $emptyAccount = Account::create([
            'family_id' => $this->family->id,
            'name' => 'Carteira Vazia',
            'type' => AccountType::Wallet,
            'initial_balance' => 0,
            'balance' => 0,
        ]);

        $deleteResponse = $this->delete(route('accounts.destroy', $emptyAccount));
        $deleteResponse->assertRedirect(route('accounts.index'));
        $this->assertDatabaseMissing('accounts', ['id' => $emptyAccount->id]);
    }

    public function test_category_full_crud(): void
    {
        // Index
        $indexResponse = $this->get(route('categories.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Categorias Financeiras');

        // Store
        $storeResponse = $this->post(route('categories.store'), [
            'name' => 'Transporte & Combustível',
            'type' => 'expense',
            'color' => '#3B82F6',
        ]);
        $storeResponse->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Transporte & Combustível']);

        $category = Category::where('name', 'Transporte & Combustível')->first();

        // Edit
        $editResponse = $this->get(route('categories.edit', $category));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Editar Categoria');

        // Update
        $updateResponse = $this->put(route('categories.update', $category), [
            'name' => 'Mobilidade & Combustível',
            'type' => 'expense',
            'color' => '#2563EB',
        ]);
        $updateResponse->assertRedirect(route('categories.index'));
        $category->refresh();
        $this->assertEquals('Mobilidade & Combustível', $category->name);

        // Destroy
        $deleteResponse = $this->delete(route('categories.destroy', $category));
        $deleteResponse->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_savings_edit_update_destroy_and_contribution_delete(): void
    {
        $goal = SavingsGoal::create([
            'family_id' => $this->family->id,
            'name' => 'Fundo de Férias',
            'target_amount' => 5000000,
            'current_amount' => 0,
            'is_active' => true,
        ]);

        // Edit
        $editResponse = $this->get(route('savings.edit', $goal));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Editar Meta de Poupança');

        // Update
        $updateResponse = $this->put(route('savings.update', $goal), [
            'name' => 'Fundo de Férias Moçambique',
            'target_amount' => '60.000,00',
            'is_active' => '1',
        ]);
        $updateResponse->assertRedirect(route('savings.index'));
        $goal->refresh();
        $this->assertEquals('Fundo de Férias Moçambique', $goal->name);
        $this->assertEquals(6000000, $goal->target_amount);

        // Add contribution & delete contribution
        $contribution = SavingsContribution::create([
            'savings_goal_id' => $goal->id,
            'amount' => 1500000,
            'contribution_date' => now()->toDateString(),
            'note' => 'Aporte 1',
        ]);
        $goal->increment('current_amount', 1500000);
        $this->assertEquals(1500000, $goal->fresh()->current_amount);

        $deleteContribResponse = $this->delete(route('savings.contributions.destroy', $contribution));
        $deleteContribResponse->assertRedirect();
        $this->assertEquals(0, $goal->fresh()->current_amount);

        // Delete goal
        $deleteGoalResponse = $this->delete(route('savings.destroy', $goal));
        $deleteGoalResponse->assertRedirect(route('savings.index'));
        $this->assertDatabaseMissing('savings_goals', ['id' => $goal->id]);
    }

    public function test_debt_edit_update_destroy_and_payment_delete(): void
    {
        $debt = Debt::create([
            'family_id' => $this->family->id,
            'creditor' => 'Banco Comercial',
            'description' => 'Empréstimo Pessoal',
            'type' => DebtType::Borrowed,
            'original_amount' => 1000000,
            'remaining_amount' => 1000000,
        ]);

        // Edit
        $editResponse = $this->get(route('debts.edit', $debt));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Editar Dívida / Crédito');

        // Update
        $updateResponse = $this->put(route('debts.update', $debt), [
            'creditor' => 'Banco Comercial de Moçambique',
            'description' => 'Empréstimo Reestruturado',
            'type' => 'borrowed',
            'original_amount' => '10.000,00',
            'remaining_amount' => '8.000,00',
            'status' => 'active',
        ]);
        $updateResponse->assertRedirect(route('debts.index'));
        $debt->refresh();
        $this->assertEquals('Empréstimo Reestruturado', $debt->description);
        $this->assertEquals(800000, $debt->remaining_amount);

        // Record payment & delete payment
        $payment = $debt->payments()->create([
            'amount' => 200000,
            'payment_date' => now()->toDateString(),
            'note' => 'Parcela',
        ]);
        $debt->update(['remaining_amount' => 600000]);

        $deletePayResponse = $this->delete(route('debts.payments.destroy', $payment));
        $deletePayResponse->assertRedirect();
        $this->assertEquals(800000, $debt->fresh()->remaining_amount);

        // Delete debt
        $deleteDebtResponse = $this->delete(route('debts.destroy', $debt));
        $deleteDebtResponse->assertRedirect(route('debts.index'));
        $this->assertDatabaseMissing('debts', ['id' => $debt->id]);
    }

    public function test_asset_full_crud(): void
    {
        // Index
        $indexResponse = $this->get(route('assets.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Património & Bens', false);

        // Store
        $storeResponse = $this->post(route('assets.store'), [
            'name' => 'Apartamento Polana',
            'type' => 'property',
            'current_value' => '3.500.000,00',
            'purchase_value' => '3.000.000,00',
            'purchase_date' => '2023-01-15',
        ]);
        $storeResponse->assertRedirect(route('assets.index'));
        $this->assertDatabaseHas('assets', ['name' => 'Apartamento Polana', 'current_value' => 350000000]);

        $asset = Asset::where('name', 'Apartamento Polana')->first();

        // Edit
        $editResponse = $this->get(route('assets.edit', $asset));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Editar Bem Patrimonial');

        // Update
        $updateResponse = $this->put(route('assets.update', $asset), [
            'name' => 'Apartamento Polana Maputo',
            'type' => 'property',
            'current_value' => '3.800.000,00',
            'purchase_value' => '3.000.000,00',
        ]);
        $updateResponse->assertRedirect(route('assets.index'));
        $asset->refresh();
        $this->assertEquals('Apartamento Polana Maputo', $asset->name);
        $this->assertEquals(380000000, $asset->current_value);

        // Destroy
        $deleteResponse = $this->delete(route('assets.destroy', $asset));
        $deleteResponse->assertRedirect(route('assets.index'));
        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }

    public function test_budget_destroy(): void
    {
        $budget = Budget::create([
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'month' => '2026-09',
            'amount' => 1000000,
            'alert_threshold' => 80,
        ]);

        $deleteResponse = $this->delete(route('budgets.destroy', $budget));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }

    public function test_financial_rule_destroy(): void
    {
        $rule = FinancialRule::create([
            'family_id' => $this->family->id,
            'name' => 'Dízimo Especial',
            'type' => FinancialRuleType::Tithe,
            'percentage' => 10,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $deleteResponse = $this->delete(route('rules.destroy', $rule));
        $deleteResponse->assertRedirect(route('rules.index'));
        $this->assertDatabaseMissing('financial_rules', ['id' => $rule->id]);
    }
}
