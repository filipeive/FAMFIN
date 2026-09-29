<?php

namespace App\Services;

use App\Enums\CategoryType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class ExcelImportService
{
    public function __construct(
        protected TransactionService $transactionService,
        protected BudgetService $budgetService
    ) {}

    /**
     * Import spreadsheet data for a family.
     *
     * @param Family $family
     * @param string $excelFilePath
     * @param User|null $user
     * @param Account|null $account
     * @return array
     */
    public function import(
        Family $family,
        string $excelFilePath,
        ?User $user = null,
        ?Account $account = null
    ): array {
        $user = $user ?? $family->users()->first();
        $account = $account ?? $family->accounts()->first();

        // Extract JSON from python parser
        $pythonBinary = file_exists('/tmp/famfin_venv/bin/python3') ? '/tmp/famfin_venv/bin/python3' : 'python3';
        $scriptPath = base_path('scripts/parse_excel.py');

        $process = new Process([$pythonBinary, $scriptPath, $excelFilePath]);
        $process->setTimeout(120);
        $process->mustRun();

        $jsonData = json_decode($process->getOutput(), true);
        if (!$jsonData || !is_array($jsonData)) {
            throw new \RuntimeException('Failed to parse spreadsheet output.');
        }

        $stats = [
            'months_processed' => 0,
            'transactions_created' => 0,
            'budgets_created' => 0,
            'categories_created' => 0,
        ];

        DB::transaction(function () use ($family, $user, $account, $jsonData, &$stats) {
            // Ensure essential categories exist
            $incomeCategory = Category::firstOrCreate(
                ['family_id' => $family->id, 'name' => 'Salário', 'type' => CategoryType::Income],
                ['icon' => '💵', 'color' => '#10B981']
            );

            $dividasCategory = Category::firstOrCreate(
                ['family_id' => $family->id, 'name' => 'Dívidas & Empréstimos', 'type' => CategoryType::Expense],
                ['icon' => '💳', 'color' => '#EF4444']
            );

            $casaCategory = Category::firstOrCreate(
                ['family_id' => $family->id, 'name' => 'Despesas da Casa', 'type' => CategoryType::Expense],
                ['icon' => '🏠', 'color' => '#3B82F6']
            );

            $familiaCategory = Category::firstOrCreate(
                ['family_id' => $family->id, 'name' => 'Apoio Familiar', 'type' => CategoryType::Expense],
                ['icon' => '👨‍👩‍👧‍👦', 'color' => '#8B5CF6']
            );

            foreach ($jsonData as $monthData) {
                $month = $monthData['month']; // e.g. 2021-04
                $transDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();

                // 1. Create Income Transaction
                $grossSalary = moneyToCents((float) $monthData['gross_salary']);
                if ($grossSalary > 0) {
                    Transaction::create([
                        'account_id' => $account->id,
                        'category_id' => $incomeCategory->id,
                        'user_id' => $user?->id,
                        'type' => TransactionType::Income,
                        'status' => TransactionStatus::Completed,
                        'amount' => $grossSalary,
                        'description' => "Salário ({$monthData['sheet_name']})",
                        'transaction_date' => $transDate,
                    ]);
                    $stats['transactions_created']++;
                }

                // 2. Process Casa items
                foreach ($monthData['sections']['Casa'] as $item) {
                    $amount = moneyToCents((float) $item['amount']);
                    if ($amount <= 0) continue;

                    $subCat = Category::firstOrCreate(
                        ['family_id' => $family->id, 'name' => $item['item'], 'type' => CategoryType::Expense],
                        ['parent_id' => $casaCategory->id, 'icon' => '🏠']
                    );

                    Transaction::create([
                        'account_id' => $account->id,
                        'category_id' => $subCat->id,
                        'user_id' => $user?->id,
                        'type' => TransactionType::Expense,
                        'status' => TransactionStatus::Completed,
                        'amount' => $amount,
                        'description' => $item['item'],
                        'notes' => $item['obs'] ?: null,
                        'transaction_date' => $transDate,
                    ]);
                    $stats['transactions_created']++;

                    // Set budget for the month
                    $this->budgetService->setBudget($family, $subCat->id, $month, $amount);
                    $stats['budgets_created']++;
                }

                // 3. Process Dividas items
                foreach ($monthData['sections']['Dividas'] as $item) {
                    $amount = moneyToCents((float) $item['amount']);
                    if ($amount <= 0) continue;

                    Transaction::create([
                        'account_id' => $account->id,
                        'category_id' => $dividasCategory->id,
                        'user_id' => $user?->id,
                        'type' => TransactionType::Expense,
                        'status' => TransactionStatus::Completed,
                        'amount' => $amount,
                        'description' => "Dívida / Empréstimo: {$item['item']}",
                        'notes' => $item['obs'] ?: null,
                        'transaction_date' => $transDate,
                    ]);
                    $stats['transactions_created']++;
                }

                // 4. Process Familia items
                foreach ($monthData['sections']['Familia'] as $item) {
                    $amount = moneyToCents((float) $item['amount']);
                    if ($amount <= 0) continue;

                    Transaction::create([
                        'account_id' => $account->id,
                        'category_id' => $familiaCategory->id,
                        'user_id' => $user?->id,
                        'type' => TransactionType::Expense,
                        'status' => TransactionStatus::Completed,
                        'amount' => $amount,
                        'description' => "Ajuda Familiar: {$item['item']}",
                        'notes' => $item['obs'] ?: null,
                        'transaction_date' => $transDate,
                    ]);
                    $stats['transactions_created']++;
                }

                $stats['months_processed']++;
            }

            // Recalculate account balance at the end
            app(BalanceService::class)->recalculateAccountBalance($account);
        });

        return $stats;
    }
}
