<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\AssetType;
use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Debt;
use App\Models\Family;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BalanceService $balanceService;
    protected Family $family;
    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->balanceService = app(BalanceService::class);

        $this->family = Family::create([
            'name' => 'Família Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
        ]);

        $this->account = Account::create([
            'family_id' => $this->family->id,
            'name' => 'Conta BCI',
            'type' => AccountType::Checking,
            'initial_balance' => 100000, // 1.000,00 MZN
            'balance' => 0, // purposely wrong to test recalculation
        ]);
    }

    public function test_recalculates_account_balance_accurately_from_ledger(): void
    {
        // Add 5.000,00 MZN income
        Transaction::create([
            'account_id' => $this->account->id,
            'type' => TransactionType::Income,
            'status' => TransactionStatus::Completed,
            'amount' => 500000,
            'description' => 'Salário',
            'transaction_date' => now()->toDateString(),
        ]);

        // Add 2.000,00 MZN expense
        Transaction::create([
            'account_id' => $this->account->id,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Completed,
            'amount' => 200000,
            'description' => 'Rancho',
            'transaction_date' => now()->toDateString(),
        ]);

        // Pending transaction should NOT affect balance
        Transaction::create([
            'account_id' => $this->account->id,
            'type' => TransactionType::Expense,
            'status' => TransactionStatus::Pending,
            'amount' => 50000,
            'description' => 'Pendente',
            'transaction_date' => now()->toDateString(),
        ]);

        $calculated = $this->balanceService->recalculateAccountBalance($this->account);

        // Expected: 1.000 + 5.000 - 2.000 = 4.000,00 MZN = 400000 centavos
        $this->assertEquals(400000, $calculated);
        $this->account->refresh();
        $this->assertEquals(400000, $this->account->balance);
    }

    public function test_calculates_family_net_worth_accurately(): void
    {
        // Account balance: 50.000,00 MZN
        $this->account->update(['balance' => 5000000, 'include_in_total' => true]);

        // Asset: Terreno/Casa worth 500.000,00 MZN
        Asset::create([
            'family_id' => $this->family->id,
            'name' => 'Terreno em Marracuene',
            'type' => AssetType::Property,
            'purchase_value' => 40000000,
            'current_value' => 50000000, // 500.000,00 MZN
        ]);

        // Debt: 20.000,00 MZN remaining
        Debt::create([
            'family_id' => $this->family->id,
            'person_name' => 'Laurinda',
            'type' => DebtType::Borrowed,
            'description' => 'Empréstimo',
            'original_amount' => 3000000,
            'remaining_amount' => 2000000, // 20.000,00 MZN
            'status' => DebtStatus::Active,
        ]);

        // Savings Goal: 15.000,00 MZN saved
        SavingsGoal::create([
            'family_id' => $this->family->id,
            'name' => 'Fundo de Emergência',
            'target_amount' => 5000000,
            'current_amount' => 1500000,
            'is_active' => true,
        ]);

        $netWorthData = $this->balanceService->getFamilyNetWorth($this->family);

        // Accounts: 50.000 + Assets: 500.000 - Debts: 20.000 = 530.000,00 MZN = 53000000 centavos
        $this->assertEquals(5000000, $netWorthData['accounts_total']);
        $this->assertEquals(50000000, $netWorthData['assets_total']);
        $this->assertEquals(2000000, $netWorthData['debts_total']);
        $this->assertEquals(1500000, $netWorthData['savings_total']);
        $this->assertEquals(53000000, $netWorthData['net_worth']);
    }
}
