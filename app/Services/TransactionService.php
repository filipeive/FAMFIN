<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransactionService
{
    public function __construct(
        protected BalanceService $balanceService,
        protected FinancialRuleService $financialRuleService
    ) {}

    /**
     * Create a new transaction (Income, Expense, or Transfer).
     *
     * @param array $data
     * @return Transaction
     * @throws InvalidArgumentException
     */
    public function createTransaction(array $data): Transaction
    {
        $amount = (int) ($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Transaction amount must be greater than zero centavos.');
        }

        $type = is_string($data['type']) ? TransactionType::from($data['type']) : $data['type'];
        $status = isset($data['status'])
            ? (is_string($data['status']) ? TransactionStatus::from($data['status']) : $data['status'])
            : TransactionStatus::Completed;

        return DB::transaction(function () use ($data, $amount, $type, $status) {
            $account = Account::findOrFail($data['account_id']);

            if ($type === TransactionType::Transfer) {
                $transferAccountId = $data['transfer_account_id'] ?? null;
                if (!$transferAccountId || $transferAccountId === $account->id) {
                    throw new InvalidArgumentException('Transfer destination account must be specified and different from source account.');
                }
                $transferAccount = Account::findOrFail($transferAccountId);
            }

            $transaction = Transaction::create([
                'account_id' => $account->id,
                'category_id' => $data['category_id'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'transfer_account_id' => $data['transfer_account_id'] ?? null,
                'type' => $type,
                'status' => $status,
                'amount' => $amount,
                'description' => $data['description'],
                'notes' => $data['notes'] ?? null,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'is_recurring' => $data['is_recurring'] ?? false,
                'recurrence_pattern' => $data['recurrence_pattern'] ?? null,
                'recurrence_end_date' => $data['recurrence_end_date'] ?? null,
                'parent_transaction_id' => $data['parent_transaction_id'] ?? null,
            ]);

            // Update balances if transaction is completed
            if ($status === TransactionStatus::Completed) {
                if ($type === TransactionType::Income) {
                    $account->increment('balance', $amount);
                } elseif ($type === TransactionType::Expense) {
                    $account->decrement('balance', $amount);
                } elseif ($type === TransactionType::Transfer) {
                    $account->decrement('balance', $amount);
                    $transferAccount->increment('balance', $amount);
                }
            }

            return $transaction;
        });
    }

    /**
     * Update an existing transaction and adjust account balances accordingly.
     *
     * @param Transaction $transaction
     * @param array $data
     * @return Transaction
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            // Revert original transaction effect on balances
            $this->revertBalanceEffect($transaction);

            // Update transaction attributes
            $transaction->update($data);
            $transaction->refresh();

            // Apply new effect on balances
            $this->applyBalanceEffect($transaction);

            return $transaction;
        });
    }

    /**
     * Delete a transaction and revert its balance effect.
     *
     * @param Transaction $transaction
     * @return bool
     */
    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            $this->revertBalanceEffect($transaction);
            return $transaction->delete();
        });
    }

    /**
     * Apply balance effect for a completed transaction.
     */
    protected function applyBalanceEffect(Transaction $transaction): void
    {
        if ($transaction->status !== TransactionStatus::Completed) {
            return;
        }

        $account = $transaction->account;
        if ($transaction->type === TransactionType::Income) {
            $account->increment('balance', $transaction->amount);
        } elseif ($transaction->type === TransactionType::Expense) {
            $account->decrement('balance', $transaction->amount);
        } elseif ($transaction->type === TransactionType::Transfer && $transaction->transfer_account_id) {
            $account->decrement('balance', $transaction->amount);
            $transaction->transferAccount?->increment('balance', $transaction->amount);
        }
    }

    /**
     * Revert balance effect for a completed transaction.
     */
    protected function revertBalanceEffect(Transaction $transaction): void
    {
        if ($transaction->status !== TransactionStatus::Completed) {
            return;
        }

        $account = $transaction->account;
        if ($transaction->type === TransactionType::Income) {
            $account->decrement('balance', $transaction->amount);
        } elseif ($transaction->type === TransactionType::Expense) {
            $account->increment('balance', $transaction->amount);
        } elseif ($transaction->type === TransactionType::Transfer && $transaction->transfer_account_id) {
            $account->increment('balance', $transaction->amount);
            $transaction->transferAccount?->decrement('balance', $transaction->amount);
        }
    }
}
