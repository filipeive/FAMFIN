<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Family;
use App\Models\Transaction;
use Carbon\Carbon;

class FinancialHealthService
{
    public function __construct(
        protected BalanceService $balanceService,
        protected BudgetService $budgetService,
        protected DebtService $debtService
    ) {}

    /**
     * Compute comprehensive financial health score (0 to 100) and diagnostics.
     */
    public function getDiagnosticReport(Family $family, string $month): array
    {
        $currency = $family->currency ?? 'MZN';

        // 1. Monthly income and expense
        $income = (int) Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->where('type', TransactionType::Income)
            ->where('status', TransactionStatus::Completed)
            ->forMonth($month)
            ->sum('amount');

        $expense = (int) Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->where('type', TransactionType::Expense)
            ->where('status', TransactionStatus::Completed)
            ->forMonth($month)
            ->sum('amount');

        // Pillar 1: Savings & Cash Surplus (25 points)
        $savingsScore = 0;
        if ($income > 0) {
            $savingsRate = max(0, ($income - $expense) / $income);
            $savingsScore = min(25, (int) round($savingsRate * 125));
        }

        // Pillar 2: Debt Ratio (25 points)
        $debtSummary = $this->debtService->getFamilyDebtSummary($family);
        $debtScore = 25;
        if ($income > 0 && $debtSummary['total_remaining'] > 0) {
            $debtRatio = $debtSummary['total_remaining'] / ($income * 12);
            $debtScore = max(0, min(25, (int) round((1 - min(1, $debtRatio)) * 25)));
        }

        // Pillar 3: Emergency Fund / Liquid Assets (25 points)
        $netWorth = $this->balanceService->getFamilyNetWorth($family);
        $liquidAccounts = $netWorth['accounts_total'];
        $fundScore = 0;
        if ($expense > 0) {
            $monthsCovered = $liquidAccounts / $expense;
            $fundScore = min(25, (int) round(($monthsCovered / 6) * 25));
        } elseif ($liquidAccounts > 0) {
            $fundScore = 25;
        }

        // Pillar 4: Budget Adherence (25 points)
        $budgetOverview = $this->budgetService->getFamilyMonthlyBudgetOverview($family, $month);
        $budgetScore = 25;
        if ($budgetOverview['total_budgeted'] > 0) {
            if ($budgetOverview['total_spent'] > $budgetOverview['total_budgeted']) {
                $over = ($budgetOverview['total_spent'] - $budgetOverview['total_budgeted']) / $budgetOverview['total_budgeted'];
                $budgetScore = max(0, (int) round((1 - min(1, $over)) * 25));
            }
        }

        $totalScore = $savingsScore + $debtScore + $fundScore + $budgetScore;

        $grade = 'Excelente';
        $gradeColor = 'emerald';
        if ($totalScore < 40) {
            $grade = 'Crítico';
            $gradeColor = 'rose';
        } elseif ($totalScore < 65) {
            $grade = 'Atenção';
            $gradeColor = 'amber';
        } elseif ($totalScore < 85) {
            $grade = 'Bom';
            $gradeColor = 'teal';
        }

        // 50/30/20 Breakdown
        $rule503020 = $this->get503020Breakdown($family, $month, $income, $expense);

        // Anomalies (>30% spike over 3-month rolling average)
        $anomalies = $this->detectSpendingAnomalies($family, $month);

        // 3-Month Cash Flow Forecast
        $forecast = $this->getThreeMonthForecast($family, $month, $income, $expense, $liquidAccounts);

        return [
            'month' => $month,
            'score' => $totalScore,
            'grade' => $grade,
            'grade_color' => $gradeColor,
            'pillars' => [
                'savings' => ['score' => $savingsScore, 'max' => 25, 'label' => 'Capacidade de Poupança'],
                'debt' => ['score' => $debtScore, 'max' => 25, 'label' => 'Controle de Endividamento'],
                'emergency_fund' => ['score' => $fundScore, 'max' => 25, 'label' => 'Reserva de Emergência'],
                'budget' => ['score' => $budgetScore, 'max' => 25, 'label' => 'Aderência Orçamental'],
            ],
            'income' => $income,
            'expense' => $expense,
            'formatted_income' => formatMoney($income, $currency),
            'formatted_expense' => formatMoney($expense, $currency),
            'rule_50_30_20' => $rule503020,
            'anomalies' => $anomalies,
            'forecast' => $forecast,
        ];
    }

    /**
     * Compute the 50/30/20 breakdown for a month.
     */
    public function get503020Breakdown(Family $family, string $month, int $income, int $expense): array
    {
        $currency = $family->currency ?? 'MZN';

        $targetNeeds = (int) round($income * 0.50);
        $targetWants = (int) round($income * 0.30);
        $targetSavings = (int) round($income * 0.20);

        $actualNeeds = (int) round($expense * 0.65);
        $actualWants = (int) round($expense * 0.35);
        $actualSavings = max(0, $income - $expense);

        return [
            'needs' => [
                'label' => 'Necessidades Básicas (50%)',
                'target' => $targetNeeds,
                'actual' => $actualNeeds,
                'percentage' => $income > 0 ? round(($actualNeeds / $income) * 100, 1) : 0,
                'formatted_target' => formatMoney($targetNeeds, $currency),
                'formatted_actual' => formatMoney($actualNeeds, $currency),
            ],
            'wants' => [
                'label' => 'Desejos & Lazer (30%)',
                'target' => $targetWants,
                'actual' => $actualWants,
                'percentage' => $income > 0 ? round(($actualWants / $income) * 100, 1) : 0,
                'formatted_target' => formatMoney($targetWants, $currency),
                'formatted_actual' => formatMoney($actualWants, $currency),
            ],
            'savings' => [
                'label' => 'Poupança & Investimento (20%)',
                'target' => $targetSavings,
                'actual' => $actualSavings,
                'percentage' => $income > 0 ? round(($actualSavings / $income) * 100, 1) : 0,
                'formatted_target' => formatMoney($targetSavings, $currency),
                'formatted_actual' => formatMoney($actualSavings, $currency),
            ],
        ];
    }

    /**
     * Detect spending spikes where current month's category expense > 130% of previous 3-month average.
     */
    public function detectSpendingAnomalies(Family $family, string $currentMonth): array
    {
        $currency = $family->currency ?? 'MZN';
        $currentDate = Carbon::parse($currentMonth . '-01');

        $prevMonths = [
            $currentDate->copy()->subMonth(1)->format('Y-m'),
            $currentDate->copy()->subMonth(2)->format('Y-m'),
            $currentDate->copy()->subMonth(3)->format('Y-m'),
        ];

        $currentExpenses = Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->where('type', TransactionType::Expense)
            ->where('status', TransactionStatus::Completed)
            ->whereNotNull('category_id')
            ->forMonth($currentMonth)
            ->selectRaw('category_id, sum(amount) as total_spent')
            ->groupBy('category_id')
            ->with('category')
            ->get();

        $anomalies = [];

        foreach ($currentExpenses as $curr) {
            $catId = $curr->category_id;
            $spentNow = (int) $curr->total_spent;

            $pastSpent = Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id))
                ->where('category_id', $catId)
                ->where('type', TransactionType::Expense)
                ->where('status', TransactionStatus::Completed)
                ->where(function ($q) use ($prevMonths) {
                    foreach ($prevMonths as $pm) {
                        $q->orWhereRaw("SUBSTR(transaction_date, 1, 7) = ?", [$pm]);
                    }
                })
                ->sum('amount');

            $pastAverage = (int) round($pastSpent / 3);

            if ($pastAverage > 0 && $spentNow > ($pastAverage * 1.3)) {
                $spikePercentage = round((($spentNow - $pastAverage) / $pastAverage) * 100, 1);
                $anomalies[] = [
                    'category_id' => $catId,
                    'category_name' => $curr->category?->name ?? 'Sem categoria',
                    'current_spent' => $spentNow,
                    'average_spent' => $pastAverage,
                    'spike_percentage' => $spikePercentage,
                    'formatted_current' => formatMoney($spentNow, $currency),
                    'formatted_average' => formatMoney($pastAverage, $currency),
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Generate a 3-month predictive cash flow forecast.
     */
    public function getThreeMonthForecast(Family $family, string $currentMonth, int $avgIncome, int $avgExpense, int $startingBalance): array
    {
        $currency = $family->currency ?? 'MZN';
        $currentDate = Carbon::parse($currentMonth . '-01');
        $netMonthly = $avgIncome - $avgExpense;

        $forecast = [];
        $runningBalance = $startingBalance;

        for ($i = 1; $i <= 3; $i++) {
            $monthDate = $currentDate->copy()->addMonths($i);
            $runningBalance += $netMonthly;

            $forecast[] = [
                'month' => $monthDate->format('Y-m'),
                'label' => $monthDate->translatedFormat('F / Y'),
                'projected_income' => $avgIncome,
                'projected_expense' => $avgExpense,
                'projected_net' => $netMonthly,
                'projected_balance' => $runningBalance,
                'formatted_projected_balance' => formatMoney($runningBalance, $currency),
                'formatted_projected_net' => formatMoney($netMonthly, $currency),
            ];
        }

        return $forecast;
    }
}
