<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BalanceService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TransactionService $transactionService;
    protected BalanceService $balanceService;
    protected Family $family;
    protected User $user;
    protected Account $checkingAccount;
    protected Account $savingsAccount;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionService = app(TransactionService::class);
        $this->balanceService = app(BalanceService::class);

        $this->family = Family::create([
            'name' => 'Família Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
        ]);

        $this->user = User::factory()->create([
            'family_id' => $this->family->id,
            'name' => 'Filipe dos Santos',
            'email' => 'filipe@famfin.app',
        ]);

        $this->checkingAccount = Account::create([
            'family_id' => $this->family->id,
            'name' => 'Conta Corrente BCI',
            'type' => AccountType::Checking,
            'initial_balance' => 1000000, // 10.000,00 MZN
            'balance' => 1000000,
        ]);

        $this->savingsAccount = Account::create([
            'family_id' => $this->family->id,
            'name' => 'Conta Poupança Millennium BIM',
            'type' => AccountType::Savings,
            'initial_balance' => 500000, // 5.000,00 MZN
            'balance' => 500000,
        ]);

        $this->category = Category::create([
            'family_id' => $this->family->id,
            'name' => 'Alimentação',
            'type' => \App\Enums\CategoryType::Expense,
        ]);
    }

    public function test_create_income_increments_account_balance(): void
    {
        $transaction = $this->transactionService->createTransaction([
            'account_id' => $this->checkingAccount->id,
            'user_id' => $this->user->id,
            'type' => TransactionType::Income,
            'amount' => 500000, // 5.000,00 MZN
            'description' => 'Salário',
        ]);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->checkingAccount->refresh();
        $this->assertEquals(1500000, $this->checkingAccount->balance); // 10k + 5k = 15k MZN
    }

    public function test_create_expense_decrements_account_balance(): void
    {
        $transaction = $this->transactionService->createTransaction([
            'account_id' => $this->checkingAccount->id,
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'type' => TransactionType::Expense,
            'amount' => 200000, // 2.000,00 MZN
            'description' => 'Compras de Supermercado',
        ]);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->checkingAccount->refresh();
        $this->assertEquals(800000, $this->checkingAccount->balance); // 10k - 2k = 8k MZN
    }

    public function test_create_transfer_moves_money_between_accounts(): void
    {
        $transaction = $this->transactionService->createTransaction([
            'account_id' => $this->checkingAccount->id,
            'transfer_account_id' => $this->savingsAccount->id,
            'user_id' => $this->user->id,
            'type' => TransactionType::Transfer,
            'amount' => 300000, // 3.000,00 MZN
            'description' => 'Transferência para poupança',
        ]);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->checkingAccount->refresh();
        $this->savingsAccount->refresh();

        $this->assertEquals(700000, $this->checkingAccount->balance); // 10k - 3k = 7k
        $this->assertEquals(800000, $this->savingsAccount->balance);  // 5k + 3k = 8k
    }

    public function test_transfer_to_same_account_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->transactionService->createTransaction([
            'account_id' => $this->checkingAccount->id,
            'transfer_account_id' => $this->checkingAccount->id,
            'type' => TransactionType::Transfer,
            'amount' => 100000,
            'description' => 'Transferência inválida',
        ]);
    }

    public function test_update_transaction_recalculates_balances_correctly(): void
    {
        $transaction = $this->transactionService->createTransaction([
            'account_id' => $this->checkingAccount->id,
            'category_id' => $this->category->id,
            'type' => TransactionType::Expense,
            'amount' => 200000, // 2.000,00 MZN
            'description' => 'Despesa Inicial',
        ]);

        $this->checkingAccount->refresh();
        $this->assertEquals(800000, $this->checkingAccount->balance);

        // Update amount from 2.000 to 1.500 MZN
        $this->transactionService->updateTransaction($transaction, [
            'amount' => 150000,
            'description' => 'Despesa Atualizada',
        ]);

        $this->checkingAccount->refresh();
        $this->assertEquals(850000, $this->checkingAccount->balance); // 10k - 1.5k = 8.5k
    }

    public function test_delete_transaction_reverts_balance_effect(): void
    {
        $transaction = $this->transactionService->createTransaction([
            'account_id' => $this->checkingAccount->id,
            'category_id' => $this->category->id,
            'type' => TransactionType::Expense,
            'amount' => 200000,
            'description' => 'Despesa para Deletar',
        ]);

        $this->checkingAccount->refresh();
        $this->assertEquals(800000, $this->checkingAccount->balance);

        $this->transactionService->deleteTransaction($transaction);

        $this->checkingAccount->refresh();
        $this->assertEquals(1000000, $this->checkingAccount->balance);
        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
    }
}
