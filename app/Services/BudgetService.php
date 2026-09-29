<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;

class BudgetService
{
    /**
     * Create or update a budget for a category in a specific month (format: YYYY-MM).
     *
     * @param Family $family
     * @param string $categoryId
     * @param string $month Format: YYYY-MM (e.g. 2026-09)
     * @param int $amount Amount in centavos
     * @param int $alertThreshold Percentage threshold for alerts (default: 80)
     * @return Budget
     */
    public function setBudget(Family $family, string $categoryId, string $month, int $amount, int $alertThreshold = 80): Budget
    {
        return Budget::updateOrCreate(
            [
                'family_id' => $family->id,
                'category_id' => $categoryId,
                'month' => $month,
            ],
            [
                'amount' => $amount,
                'alert_threshold' => $alertThreshold,
            ]
        );
    }

    /**
     * Get detailed status of a category budget in a month.
     *
     * @param Family $family
     * @param string $categoryId
     * @param string $month
     * @return array
     */
    public function getCategoryBudgetStatus(Family $family, string $categoryId, string $month): array
    {
        $budget = Budget::where('family_id', $family->id)
            ->where('category_id', $categoryId)
            ->where('month', $month)
            ->first();

        $category = Category::find($categoryId);

        $spent = (int) Transaction::where('category_id', $categoryId)
            ->whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->where('type', TransactionType::Expense)
            ->where('status', TransactionStatus::Completed)
            ->forMonth($month)
            ->sum('amount');

        $budgetAmount = $budget ? $budget->amount : 0;
        $remaining = $budgetAmount - $spent;
        $percentage = $budgetAmount > 0 ? round(($spent / $budgetAmount) * 100, 1) : 0.0;
        $threshold = $budget ? $budget->alert_threshold : 80;

        $status = 'no_budget';
        if ($budgetAmount > 0) {
            if ($spent > $budgetAmount) {
                $status = 'exceeded';
            } elseif ($percentage >= $threshold) {
                $status = 'warning';
            } else {
                $status = 'ok';
            }
        }

        $currency = $family->currency ?? 'MZN';

        return [
            'category_id' => $categoryId,
            'category_name' => $category?->name,
            'category_icon' => $category?->icon,
            'month' => $month,
            'budget_amount' => $budgetAmount,
            'spent_amount' => $spent,
            'remaining_amount' => $remaining,
            'percentage_used' => $percentage,
            'alert_threshold' => $threshold,
            'status' => $status,
            'formatted_budget' => formatMoney($budgetAmount, $currency),
            'formatted_spent' => formatMoney($spent, $currency),
            'formatted_remaining' => formatMoney($remaining, $currency),
        ];
    }

    /**
     * Get complete monthly budget overview for a family.
     *
     * @param Family $family
     * @param string $month Format: YYYY-MM
     * @return array
     */
    public function getFamilyMonthlyBudgetOverview(Family $family, string $month): array
    {
        $budgets = Budget::with('category')
            ->where('family_id', $family->id)
            ->where('month', $month)
            ->get();

        $items = [];
        $totalBudgeted = 0;
        $totalSpent = 0;

        foreach ($budgets as $b) {
            $status = $this->getCategoryBudgetStatus($family, $b->category_id, $month);
            $items[] = $status;
            $totalBudgeted += $status['budget_amount'];
            $totalSpent += $status['spent_amount'];
        }

        $currency = $family->currency ?? 'MZN';
        $totalRemaining = $totalBudgeted - $totalSpent;
        $overallPercentage = $totalBudgeted > 0 ? round(($totalSpent / $totalBudgeted) * 100, 1) : 0.0;

        return [
            'month' => $month,
            'items' => $items,
            'total_budgeted' => $totalBudgeted,
            'total_spent' => $totalSpent,
            'total_remaining' => $totalRemaining,
            'overall_percentage' => $overallPercentage,
            'formatted_total_budgeted' => formatMoney($totalBudgeted, $currency),
            'formatted_total_spent' => formatMoney($totalSpent, $currency),
            'formatted_total_remaining' => formatMoney($totalRemaining, $currency),
        ];
    }

    /**
     * Copy all budgets from one month to another.
     *
     * @param Family $family
     * @param string $fromMonth
     * @param string $toMonth
     * @return int Number of budgets copied
     */
    public function copyBudgetsToNextMonth(Family $family, string $fromMonth, string $toMonth): int
    {
        $sourceBudgets = Budget::where('family_id', $family->id)
            ->where('month', $fromMonth)
            ->get();

        $count = 0;
        foreach ($sourceBudgets as $src) {
            Budget::updateOrCreate(
                [
                    'family_id' => $family->id,
                    'category_id' => $src->category_id,
                    'month' => $toMonth,
                ],
                [
                    'amount' => $src->amount,
                    'alert_threshold' => $src->alert_threshold,
                ]
            );
            $count++;
        }

        return $count;
    }
}
