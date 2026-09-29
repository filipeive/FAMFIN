<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SavingsService
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    /**
     * Add a contribution to a savings goal.
     *
     * @param SavingsGoal $goal
     * @param int $amount Amount in centavos
     * @param int|null $userId
     * @param string|null $date
     * @param string|null $note
     * @param Account|null $fromAccount Optional account to debit
     * @return SavingsContribution
     */
    public function addContribution(
        SavingsGoal $goal,
        int $amount,
        ?int $userId = null,
        ?string $date = null,
        ?string $note = null,
        ?Account $fromAccount = null
    ): SavingsContribution {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Contribution amount must be greater than zero centavos.');
        }

        return DB::transaction(function () use ($goal, $amount, $userId, $date, $note, $fromAccount) {
            $contribution = SavingsContribution::create([
                'savings_goal_id' => $goal->id,
                'user_id' => $userId,
                'amount' => $amount,
                'contribution_date' => $date ?? now()->toDateString(),
                'note' => $note,
            ]);

            $goal->increment('current_amount', $amount);

            if ($fromAccount) {
                $this->transactionService->createTransaction([
                    'account_id' => $fromAccount->id,
                    'user_id' => $userId,
                    'type' => TransactionType::Expense,
                    'amount' => $amount,
                    'description' => "Contribuição para meta de poupança: {$goal->name}",
                    'notes' => $note,
                    'transaction_date' => $date ?? now()->toDateString(),
                ]);
            }

            return $contribution;
        });
    }

    /**
     * Get detailed progress metrics for a savings goal.
     *
     * @param SavingsGoal $goal
     * @return array
     */
    public function getGoalProgress(SavingsGoal $goal): array
    {
        $currency = $goal->family->currency ?? 'MZN';
        $remaining = max(0, $goal->target_amount - $goal->current_amount);
        $percentage = $goal->target_amount > 0 ? round(($goal->current_amount / $goal->target_amount) * 100, 1) : 0.0;
        $isCompleted = $goal->current_amount >= $goal->target_amount;

        $monthlyNeeded = null;
        if ($goal->deadline && !$isCompleted) {
            $monthsLeft = now()->diffInMonths($goal->deadline);
            $monthlyNeeded = $monthsLeft > 0 ? (int) ceil($remaining / $monthsLeft) : $remaining;
        }

        return [
            'goal_id' => $goal->id,
            'name' => $goal->name,
            'target_amount' => $goal->target_amount,
            'current_amount' => $goal->current_amount,
            'remaining' => $remaining,
            'progress_percentage' => $percentage,
            'is_completed' => $isCompleted,
            'monthly_needed' => $monthlyNeeded,
            'deadline' => $goal->deadline?->format('d/m/Y'),
            'formatted_target' => formatMoney($goal->target_amount, $currency),
            'formatted_current' => formatMoney($goal->current_amount, $currency),
            'formatted_remaining' => formatMoney($remaining, $currency),
            'formatted_monthly_needed' => $monthlyNeeded ? formatMoney($monthlyNeeded, $currency) : null,
        ];
    }
}
