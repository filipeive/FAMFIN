<?php

namespace App\Services;

use App\Enums\DebtStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Family;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DebtService
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    /**
     * Record a debt payment / amortization.
     *
     * @param Debt $debt
     * @param int $amount Amount in centavos
     * @param int|null $userId
     * @param string|null $date
     * @param string|null $note
     * @param Account|null $fromAccount Optional account to debit
     * @return DebtPayment
     */
    public function recordPayment(
        Debt $debt,
        int $amount,
        ?int $userId = null,
        ?string $date = null,
        ?string $note = null,
        ?Account $fromAccount = null
    ): DebtPayment {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero centavos.');
        }

        return DB::transaction(function () use ($debt, $amount, $userId, $date, $note, $fromAccount) {
            $payment = DebtPayment::create([
                'debt_id' => $debt->id,
                'user_id' => $userId,
                'amount' => $amount,
                'payment_date' => $date ?? now()->toDateString(),
                'note' => $note,
            ]);

            $newRemaining = max(0, $debt->remaining_amount - $amount);
            $newStatus = $newRemaining === 0 ? DebtStatus::Paid : $debt->status;

            $debt->update([
                'remaining_amount' => $newRemaining,
                'status' => $newStatus,
            ]);

            if ($fromAccount) {
                $this->transactionService->createTransaction([
                    'account_id' => $fromAccount->id,
                    'user_id' => $userId,
                    'type' => TransactionType::Expense,
                    'amount' => $amount,
                    'description' => "Amortização de dívida: {$debt->description}",
                    'notes' => $note,
                    'transaction_date' => $date ?? now()->toDateString(),
                ]);
            }

            return $payment;
        });
    }

    /**
     * Get complete debt summary for a family.
     *
     * @param Family $family
     * @return array
     */
    public function getFamilyDebtSummary(Family $family): array
    {
        $currency = $family->currency ?? 'MZN';

        $activeDebts = $family->debts()
            ->whereIn('status', [DebtStatus::Active, DebtStatus::Overdue])
            ->get();

        $totalOriginal = (int) $family->debts()->sum('original_amount');
        $totalRemaining = (int) $activeDebts->sum('remaining_amount');
        $totalPaid = (int) DebtPayment::whereIn('debt_id', $family->debts()->pluck('id'))->sum('amount');
        $progress = $totalOriginal > 0 ? round(($totalPaid / $totalOriginal) * 100, 1) : 0.0;

        $overdueCount = $family->debts()
            ->where('status', DebtStatus::Overdue)
            ->count();

        return [
            'total_original' => $totalOriginal,
            'total_remaining' => $totalRemaining,
            'total_paid' => $totalPaid,
            'progress_percentage' => $progress,
            'active_debts_count' => $activeDebts->count(),
            'overdue_count' => $overdueCount,
            'formatted_total_original' => formatMoney($totalOriginal, $currency),
            'formatted_total_remaining' => formatMoney($totalRemaining, $currency),
            'formatted_total_paid' => formatMoney($totalPaid, $currency),
        ];
    }

    /**
     * Delete a debt and all its payments.
     *
     * @param Debt $debt
     * @return void
     */
    public function deleteDebt(Debt $debt): void
    {
        DB::transaction(function () use ($debt) {
            $debt->payments()->delete();
            $debt->delete();
        });
    }

    /**
     * Delete a debt payment and revert the remaining amount on the debt.
     *
     * @param DebtPayment $payment
     * @return void
     */
    public function deletePayment(DebtPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $debt = $payment->debt;
            if ($debt) {
                $newRemaining = $debt->remaining_amount + $payment->amount;
                $newStatus = ($debt->due_date && $debt->due_date->isPast() && $newRemaining > 0)
                    ? DebtStatus::Overdue
                    : DebtStatus::Active;

                $debt->update([
                    'remaining_amount' => $newRemaining,
                    'status' => $newStatus,
                ]);
            }
            $payment->delete();
        });
    }
}
