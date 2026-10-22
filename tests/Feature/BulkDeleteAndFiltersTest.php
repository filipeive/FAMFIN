<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkDeleteAndFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected Family $family;
    protected Account $account;

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
            'name' => 'Conta à Ordem BIM',
            'type' => AccountType::Checking,
            'currency' => 'MZN',
            'initial_balance' => 1000000,
            'balance' => 1000000,
            'is_active' => true,
        ]);
    }

    public function test_can_bulk_delete_transactions_and_revert_balances(): void
    {
        $t1 = Transaction::create([
            'account_id' => $this->account->id,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 100000, // 1,000 MZN
            'description' => 'Despesa 1',
            'transaction_date' => '2026-09-01',
        ]);
        $this->account->decrement('balance', 100000);

        $t2 = Transaction::create([
            'account_id' => $this->account->id,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 200000, // 2,000 MZN
            'description' => 'Despesa 2',
            'transaction_date' => '2026-09-02',
        ]);
        $this->account->decrement('balance', 200000);

        $t3 = Transaction::create([
            'account_id' => $this->account->id,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 300000, // 3,000 MZN
            'description' => 'Despesa 3',
            'transaction_date' => '2026-09-03',
        ]);
        $this->account->decrement('balance', 300000);

        $this->account->refresh();
        $this->assertEquals(400000, $this->account->balance);

        // Bulk delete t1 and t2
        $response = $this->post(route('transactions.bulk_delete'), [
            'transaction_ids' => [$t1->id, $t2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', '2 transações eliminadas com sucesso!');

        // t1 and t2 soft deleted
        $this->assertSoftDeleted('transactions', ['id' => $t1->id]);
        $this->assertSoftDeleted('transactions', ['id' => $t2->id]);

        // t3 not deleted
        $this->assertDatabaseHas('transactions', [
            'id' => $t3->id,
            'deleted_at' => null,
        ]);

        // Account balance reverted by 100,000 + 200,000 = 300,000 centavos => 700,000
        $this->account->refresh();
        $this->assertEquals(700000, $this->account->balance);
    }

    public function test_can_filter_transactions_on_index(): void
    {
        Transaction::create([
            'account_id' => $this->account->id,
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 500000,
            'description' => 'Salário Especial',
            'transaction_date' => '2026-09-10',
        ]);

        Transaction::create([
            'account_id' => $this->account->id,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 50000,
            'description' => 'Combustível Teste',
            'transaction_date' => '2026-09-15',
        ]);

        // Filter by type=income
        $response = $this->get(route('transactions.index', ['type' => 'income']));
        $response->assertStatus(200);
        $response->assertSee('Salário Especial');
        $response->assertDontSee('Combustível Teste');

        // Filter by search=Combustível
        $response = $this->get(route('transactions.index', ['search' => 'Combustível']));
        $response->assertStatus(200);
        $response->assertSee('Combustível Teste');
        $response->assertDontSee('Salário Especial');
    }
}
