<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Family;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use App\Models\User;
use App\Services\SavingsService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavingsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SavingsService $savingsService;
    protected Family $family;
    protected User $user;
    protected Account $account;
    protected SavingsGoal $goal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savingsService = app(SavingsService::class);

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

        $this->goal = SavingsGoal::create([
            'family_id' => $this->family->id,
            'name' => 'Fundo de Emergência',
            'target_amount' => 5000000, // 50.000,00 MZN
            'current_amount' => 0,
            'is_active' => true,
        ]);
    }

    public function test_add_contribution_increments_goal_and_creates_record(): void
    {
        $contribution = $this->savingsService->addContribution(
            $this->goal,
            500000, // 5.000,00 MZN
            $this->user->id,
            '2026-09-29',
            'Depósito mensal',
            $this->account
        );

        $this->assertInstanceOf(SavingsContribution::class, $contribution);
        $this->goal->refresh();
        $this->assertEquals(500000, $this->goal->current_amount);

        // Account balance should decrease by 5.000,00 MZN
        $this->account->refresh();
        $this->assertEquals(500000, $this->account->balance);
    }

    public function test_goal_progress_calculation(): void
    {
        $this->goal->update([
            'target_amount' => 10000000, // 100.000,00 MZN
            'current_amount' => 2500000,  // 25.000,00 MZN (25%)
            'deadline' => now()->addMonths(5)->toDateString(),
        ]);

        $progress = $this->savingsService->getGoalProgress($this->goal);

        $this->assertEquals(25.0, $progress['progress_percentage']);
        $this->assertEquals(7500000, $progress['remaining']);
        $this->assertFalse($progress['is_completed']);
        $this->assertEquals(1500000, $progress['monthly_needed']); // 75.000 / 5 = 15.000 MZN/month
    }
}
