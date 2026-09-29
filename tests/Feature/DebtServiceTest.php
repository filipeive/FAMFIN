<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Family;
use App\Models\User;
use App\Services\DebtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DebtService $debtService;
    protected Family $family;
    protected User $user;
    protected Account $account;
    protected Debt $debt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->debtService = app(DebtService::class);

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

        $this->account = Account::create([
            'family_id' => $this->family->id,
            'name' => 'Conta BCI',
            'type' => AccountType::Checking,
            'initial_balance' => 1000000, // 10.000,00 MZN
            'balance' => 1000000,
        ]);

        $this->debt = Debt::create([
            'family_id' => $this->family->id,
            'person_name' => 'Laurinda',
            'type' => DebtType::Borrowed,
            'description' => 'Empréstimo Família',
            'original_amount' => 500000, // 5.000,00 MZN
            'remaining_amount' => 500000,
            'status' => DebtStatus::Active,
        ]);
    }

    public function test_record_partial_payment_reduces_remaining_amount(): void
    {
        $payment = $this->debtService->recordPayment(
            $this->debt,
            200000, // 2.000,00 MZN
            $this->user->id,
            '2026-09-29',
            'Primeira parcela',
            $this->account
        );

        $this->assertInstanceOf(DebtPayment::class, $payment);
        $this->debt->refresh();
        $this->assertEquals(300000, $this->debt->remaining_amount); // 3.000,00 MZN left
        $this->assertEquals(DebtStatus::Active, $this->debt->status);

        // Account balance decreased
        $this->account->refresh();
        $this->assertEquals(800000, $this->account->balance);
    }

    public function test_full_payment_sets_status_to_paid(): void
    {
        $this->debtService->recordPayment(
            $this->debt,
            500000, // Full 5.000,00 MZN
            $this->user->id,
            '2026-09-29',
            'Pagamento total',
            $this->account
        );

        $this->debt->refresh();
        $this->assertEquals(0, $this->debt->remaining_amount);
        $this->assertEquals(DebtStatus::Paid, $this->debt->status);
    }

    public function test_family_debt_summary(): void
    {
        // Second debt
        Debt::create([
            'family_id' => $this->family->id,
            'person_name' => 'Nico',
            'type' => DebtType::Borrowed,
            'description' => 'Empréstimo Nico',
            'original_amount' => 400000,
            'remaining_amount' => 400000,
            'status' => DebtStatus::Active,
        ]);

        $summary = $this->debtService->getFamilyDebtSummary($this->family);

        // Total original = 5.000 + 4.000 = 9.000,00 MZN = 900000 centavos
        $this->assertEquals(900000, $summary['total_original']);
        $this->assertEquals(900000, $summary['total_remaining']);
        $this->assertEquals(2, $summary['active_debts_count']);
    }
}
