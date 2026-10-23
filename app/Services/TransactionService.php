<?php

namespace App\Services;

use App\Enums\CategoryType;
use App\Enums\FinancialRuleType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
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
     * Also cascades to child deduction transactions if any.
     *
     * @param Transaction $transaction
     * @return bool
     */
    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            foreach ($transaction->childTransactions as $child) {
                $this->revertBalanceEffect($child);
                $child->delete();
            }

            $this->revertBalanceEffect($transaction);
            return $transaction->delete();
        });
    }

    /**
     * Delete multiple transactions and revert their balance effects.
     *
     * @param array $transactionIds
     * @return int Count of deleted transactions
     */
    public function bulkDeleteTransactions(array $transactionIds): int
    {
        return DB::transaction(function () use ($transactionIds) {
            $transactions = Transaction::whereIn('id', $transactionIds)->get();
            $count = 0;
            foreach ($transactions as $transaction) {
                foreach ($transaction->childTransactions as $child) {
                    $this->revertBalanceEffect($child);
                    $child->delete();
                }
                $this->revertBalanceEffect($transaction);
                if ($transaction->delete()) {
                    $count++;
                }
            }
            return $count;
        });
    }

    /**
     * Create an income transaction with optional automatic deductions (Dízimo and/or INSS).
     *
     * @param array $incomeData
     * @param bool $deductTithe
     * @param bool $deductInss
     * @return array ['income' => Transaction, 'deductions' => Transaction[], 'net_amount' => int]
     * @throws InvalidArgumentException
     */
    public function createIncomeWithDeductions(
        array $incomeData,
        bool $deductTithe = false,
        bool $deductInss = false
    ): array {
        $amount = (int) ($incomeData['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Income amount must be greater than zero centavos.');
        }

        $incomeData['type'] = TransactionType::Income;

        return DB::transaction(function () use ($incomeData, $amount, $deductTithe, $deductInss) {
            $incomeTransaction = $this->createTransaction($incomeData);
            $account = Account::findOrFail($incomeData['account_id']);
            $family = $account->family;
            $deductions = [];

            $inssAmount = 0;

            // 1. Process INSS (default 3% or active rule)
            if ($deductInss) {
                $inssRule = $family?->financialRules()
                    ->where('type', FinancialRuleType::Inss)
                    ->where('is_active', true)
                    ->first();

                $inssRate = $inssRule ? (float) $inssRule->percentage : 3.00;
                $inssAmount = (int) floor($amount * ($inssRate / 100));

                if ($inssAmount > 0) {
                    $inssCategory = Category::where('family_id', $account->family_id)
                        ->where('type', CategoryType::Expense)
                        ->where(function ($q) {
                            $q->where('name', 'like', '%INSS%')
                              ->orWhere('name', 'like', '%inss%');
                        })->first();

                    if (!$inssCategory) {
                        $inssCategory = Category::create([
                            'family_id' => $account->family_id,
                            'name' => 'INSS & Encargos Sociais',
                            'type' => CategoryType::Expense,
                            'icon' => '⚖️',
                            'color' => '#64748B',
                            'is_system' => true,
                        ]);
                    }

                    $dateString = is_string($incomeTransaction->transaction_date)
                        ? $incomeTransaction->transaction_date
                        : $incomeTransaction->transaction_date->toDateString();

                    $inssTransaction = $this->createTransaction([
                        'account_id' => $account->id,
                        'category_id' => $inssCategory->id,
                        'user_id' => $incomeData['user_id'] ?? null,
                        'type' => TransactionType::Expense,
                        'status' => $incomeTransaction->status,
                        'amount' => $inssAmount,
                        'description' => "INSS ({$inssRate}%) — " . $incomeTransaction->description,
                        'notes' => "Dedução automática legal de INSS ({$inssRate}%).",
                        'transaction_date' => $dateString,
                        'parent_transaction_id' => $incomeTransaction->id,
                    ]);

                    $deductions[] = $inssTransaction;
                }
            }

            // 2. Process Tithe / Dízimo (default 10% or active rule)
            if ($deductTithe) {
                $titheRule = $family?->financialRules()
                    ->where('type', FinancialRuleType::Tithe)
                    ->where('is_active', true)
                    ->first();

                $titheRate = $titheRule ? (float) $titheRule->percentage : 10.00;
                $baseConfig = $titheRule?->config['base'] ?? 'gross';
                $titheBase = ($baseConfig === 'net_after_inss' && $inssAmount > 0)
                    ? ($amount - $inssAmount)
                    : $amount;

                $titheAmount = (int) floor($titheBase * ($titheRate / 100));

                if ($titheAmount > 0) {
                    $titheCategory = Category::where('family_id', $account->family_id)
                        ->where('type', CategoryType::Expense)
                        ->where(function ($q) {
                            $q->where('name', 'like', '%Dízimo%')
                              ->orWhere('name', 'like', '%dizimo%')
                              ->orWhere('name', 'like', '%Dízimos%')
                              ->orWhere('name', 'like', '%dízimos%')
                              ->orWhere('name', 'like', '%Igreja%');
                        })->first();

                    if (!$titheCategory) {
                        $titheCategory = Category::create([
                            'family_id' => $account->family_id,
                            'name' => 'Dízimos & Contribuições',
                            'type' => CategoryType::Expense,
                            'icon' => '⛪',
                            'color' => '#8B5CF6',
                            'is_system' => true,
                        ]);
                    }

                    $dateString = is_string($incomeTransaction->transaction_date)
                        ? $incomeTransaction->transaction_date
                        : $incomeTransaction->transaction_date->toDateString();

                    $titheTransaction = $this->createTransaction([
                        'account_id' => $account->id,
                        'category_id' => $titheCategory->id,
                        'user_id' => $incomeData['user_id'] ?? null,
                        'type' => TransactionType::Expense,
                        'status' => $incomeTransaction->status,
                        'amount' => $titheAmount,
                        'description' => "Dízimo ({$titheRate}%) — " . $incomeTransaction->description,
                        'notes' => "Dedução automática de Dízimo ({$titheRate}%).",
                        'transaction_date' => $dateString,
                        'parent_transaction_id' => $incomeTransaction->id,
                    ]);

                    $deductions[] = $titheTransaction;
                }
            }

            $totalDeductions = array_sum(array_map(fn($d) => $d->amount, $deductions));

            return [
                'income' => $incomeTransaction,
                'deductions' => $deductions,
                'total_deductions' => $totalDeductions,
                'net_amount' => $amount - $totalDeductions,
            ];
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
     * Allocate a monthly allowance to a sub-account / child envelope.
     * Debits the source account as an Expense (Category: Mesadas).
     * Credits the sub-account as an Income.
     * Atomically links both transactions.
     *
     * @param Account $subAccount
     * @param Account $sourceAccount
     * @param int $amount Amount in centavos
     * @param string $date
     * @param string|null $note
     * @param int|null $userId
     * @return array{expense: Transaction, income: Transaction}
     */
    public function allocateAllowance(
        Account $subAccount,
        Account $sourceAccount,
        int $amount,
        string $date,
        ?string $note = null,
        ?int $userId = null
    ): array {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Allowance amount must be greater than zero centavos.');
        }

        $mesadaCategory = Category::where('family_id', $sourceAccount->family_id)
            ->where('type', CategoryType::Expense)
            ->where(function ($q) {
                $q->where('name', 'like', '%Mesada%')
                  ->orWhere('name', 'like', '%mesada%')
                  ->orWhere('name', 'like', '%Filhos%')
                  ->orWhere('name', 'like', '%Dependentes%');
            })->first();

        if (!$mesadaCategory) {
            $mesadaCategory = Category::create([
                'family_id' => $sourceAccount->family_id,
                'name' => 'Mesadas & Dependentes',
                'type' => CategoryType::Expense,
                'icon' => '👧',
                'color' => '#C084FC',
            ]);
        }

        return DB::transaction(function () use ($subAccount, $sourceAccount, $amount, $date, $note, $userId, $mesadaCategory) {
            $person = $subAccount->assigned_to_name ?? $subAccount->name;

            // 1. Expense transaction on source/main account
            $expenseTx = $this->createTransaction([
                'account_id' => $sourceAccount->id,
                'category_id' => $mesadaCategory?->id,
                'user_id' => $userId,
                'type' => TransactionType::Expense,
                'amount' => $amount,
                'description' => "Mesada - {$person}",
                'notes' => $note ?? "Atribuição mensal de mesada para {$person}",
                'transaction_date' => $date,
            ]);

            // 2. Income/Credit transaction on the sub-account
            $incomeTx = $this->createTransaction([
                'account_id' => $subAccount->id,
                'category_id' => null,
                'user_id' => $userId,
                'type' => TransactionType::Income,
                'amount' => $amount,
                'description' => "Aporte de Mesada Mensal",
                'notes' => $note ?? "Crédito de mesada financiado por {$sourceAccount->name}",
                'transaction_date' => $date,
                'parent_transaction_id' => $expenseTx->id,
            ]);

            return [
                'expense' => $expenseTx,
                'income' => $incomeTx,
            ];
        });
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
