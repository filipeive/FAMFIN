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

    public function test_create_income_with_tithe_deduction(): void
    {
        $result = $this->transactionService->createIncomeWithDeductions([
            'account_id' => $this->checkingAccount->id,
            'user_id' => $this->user->id,
            'amount' => 5000000, // 50.000,00 MZN
            'description' => 'Trabalho Extra Freelance',
            'transaction_date' => now()->toDateString(),
        ], deductTithe: true, deductInss: false);

        $this->assertArrayHasKey('income', $result);
        $this->assertArrayHasKey('deductions', $result);
        $this->assertCount(1, $result['deductions']);

        $income = $result['income'];
        $tithe = $result['deductions'][0];

        $this->assertEquals(5000000, $income->amount);
        $this->assertEquals(500000, $tithe->amount); // 10% de 50.000,00 = 5.000,00 MZN
        $this->assertEquals($income->id, $tithe->parent_transaction_id);
        $this->assertEquals(TransactionType::Expense, $tithe->type);

        $this->checkingAccount->refresh();
        // Initial: 10.000,00 + 50.000,00 (income) - 5.000,00 (tithe) = 55.000,00 MZN (5500000 centavos)
        $this->assertEquals(5500000, $this->checkingAccount->balance);
    }

    public function test_create_income_with_life_child_deductions_tithe_and_inss(): void
    {
        $result = $this->transactionService->createIncomeWithDeductions([
            'account_id' => $this->checkingAccount->id,
            'user_id' => $this->user->id,
            'amount' => 10000000, // 100.000,00 MZN
            'description' => 'Salário Life Child',
            'transaction_date' => now()->toDateString(),
        ], deductTithe: true, deductInss: true);

        $this->assertCount(2, $result['deductions']);

        $income = $result['income'];
        $inss = collect($result['deductions'])->first(fn($t) => str_contains($t->description, 'INSS'));
        $tithe = collect($result['deductions'])->first(fn($t) => str_contains($t->description, 'Dízimo'));

        $this->assertNotNull($inss);
        $this->assertNotNull($tithe);

        // INSS 3% = 300.000 centavos (3.000,00 MZN)
        $this->assertEquals(300000, $inss->amount);
        // Tithe 10% = 1.000.000 centavos (10.000,00 MZN)
        $this->assertEquals(1000000, $tithe->amount);

        $this->checkingAccount->refresh();
        // Initial 10.000,00 + 100.000,00 - 3.000,00 - 10.000,00 = 97.000,00 MZN (9.700.000 centavos)
        $this->assertEquals(9700000, $this->checkingAccount->balance);

        // Test cascade deletion: deleting parent income reverts both deductions and restores balance
        $this->transactionService->deleteTransaction($income);

        $this->checkingAccount->refresh();
        $this->assertEquals(1000000, $this->checkingAccount->balance); // Back to initial 10.000,00 MZN
        $this->assertSoftDeleted('transactions', ['id' => $income->id]);
        $this->assertSoftDeleted('transactions', ['id' => $inss->id]);
        $this->assertSoftDeleted('transactions', ['id' => $tithe->id]);
    }
}
