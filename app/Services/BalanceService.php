<?php

namespace App\Services;

use App\Enums\DebtStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Family;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    /**
     * Recalculate and update the exact balance of an account based on its completed transactions.
     *
     * @param Account $account
     * @return int New balance in centavos
     */
    public function recalculateAccountBalance(Account $account): int
    {
        return DB::transaction(function () use ($account) {
            $incomes = (int) Transaction::where('account_id', $account->id)
                ->where('type', TransactionType::Income)
                ->where('status', TransactionStatus::Completed)
                ->sum('amount');

            $expenses = (int) Transaction::where('account_id', $account->id)
                ->where('type', TransactionType::Expense)
                ->where('status', TransactionStatus::Completed)
                ->sum('amount');

            $transfersOut = (int) Transaction::where('account_id', $account->id)
                ->where('type', TransactionType::Transfer)
                ->where('status', TransactionStatus::Completed)
                ->sum('amount');

            $transfersIn = (int) Transaction::where('transfer_account_id', $account->id)
                ->where('type', TransactionType::Transfer)
                ->where('status', TransactionStatus::Completed)
                ->sum('amount');

            $newBalance = (int) ($account->initial_balance + $incomes - $expenses - $transfersOut + $transfersIn);

            $account->update(['balance' => $newBalance]);

            return $newBalance;
        });
    }

    /**
     * Recalculate balances for all accounts in a family.
     *
     * @param Family $family
     * @return array<string, int> Account ID => New Balance
     */
    public function recalculateFamilyAccounts(Family $family): array
    {
        $balances = [];
        foreach ($family->accounts as $account) {
            $balances[$account->id] = $this->recalculateAccountBalance($account);
        }
        return $balances;
    }

    /**
     * Get the complete Net Worth (Património Líquido) summary for a family.
     *
     * @param Family $family
     * @return array
     */
    public function getFamilyNetWorth(Family $family): array
    {
        $accountsTotal = (int) $family->accounts()
            ->where('is_active', true)
            ->where('include_in_total', true)
            ->sum('balance');

        $assetsTotal = (int) $family->assets()
            ->sum('current_value');

        $debtsTotal = (int) $family->debts()
            ->whereIn('status', [DebtStatus::Active, DebtStatus::Overdue])
            ->sum('remaining_amount');

        $savingsTotal = (int) $family->savingsGoals()
            ->where('is_active', true)
            ->sum('current_amount');

        $netWorth = ($accountsTotal + $assetsTotal) - $debtsTotal;

        $currency = $family->currency ?? 'MZN';

        return [
            'accounts_total' => $accountsTotal,
            'assets_total' => $assetsTotal,
            'debts_total' => $debtsTotal,
            'savings_total' => $savingsTotal,
            'net_worth' => $netWorth,
            'formatted_accounts_total' => formatMoney($accountsTotal, $currency),
            'formatted_assets_total' => formatMoney($assetsTotal, $currency),
            'formatted_debts_total' => formatMoney($debtsTotal, $currency),
            'formatted_savings_total' => formatMoney($savingsTotal, $currency),
            'formatted_net_worth' => formatMoney($netWorth, $currency),
        ];
    }
}
