<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Transaction;
use App\Services\BalanceService;
use App\Services\BudgetService;
use App\Services\DebtService;
use App\Services\FinancialRuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        protected BalanceService $balanceService,
        protected BudgetService $budgetService,
        protected FinancialRuleService $ruleService,
        protected DebtService $debtService
    ) {}

    public function index(Request $request)
    {
        $family = Family::with(['accounts', 'categories', 'financialRules'])->first();
        if (!$family) {
            abort(404, 'Nenhuma família configurada. Execute o seeder do banco de dados.');
        }

        // Determine active month (default: latest transaction month or current month)
        $latestTransDate = Transaction::latest('transaction_date')->value('transaction_date');
        $defaultMonth = $latestTransDate ? Carbon::parse($latestTransDate)->format('Y-m') : now()->format('Y-m');
        $month = $request->get('month', $defaultMonth);

        // 1. Month dates
        $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        // 2. Net Worth
        $netWorth = $this->balanceService->getFamilyNetWorth($family);

        // 3. Month Incomes & Expenses
        $monthIncomes = (int) Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->incomes()
            ->completed()
            ->forMonth($month)
            ->sum('amount');

        $monthExpenses = (int) Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->expenses()
            ->completed()
            ->forMonth($month)
            ->sum('amount');

        // 4. Financial Rules Breakdown on Month Income
        $incomeBreakdown = $this->ruleService->calculateBreakdown($family, $monthIncomes);

        // 5. Monthly Budget Overview
        $budgetOverview = $this->budgetService->getFamilyMonthlyBudgetOverview($family, $month);

        // 6. Debts Summary
        $debtSummary = $this->debtService->getFamilyDebtSummary($family);

        // 7. Recent Transactions for the month
        $recentTransactions = Transaction::with(['category', 'account', 'transferAccount'])
            ->whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->forMonth($month)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // 8. Available months in system for selector
        $availableMonths = Transaction::selectRaw("DISTINCT SUBSTR(transaction_date, 1, 7) as m")
            ->orderBy('m', 'desc')
            ->pluck('m')
            ->filter()
            ->toArray();

        if (empty($availableMonths)) {
            $availableMonths = [$month];
        }

        $currency = $family->currency ?? 'MZN';
        $monthBalance = $monthIncomes - $monthExpenses;

        return view('dashboard', compact(
            'family',
            'month',
            'netWorth',
            'monthIncomes',
            'monthExpenses',
            'monthBalance',
            'incomeBreakdown',
            'budgetOverview',
            'debtSummary',
            'recentTransactions',
            'availableMonths',
            'currency'
        ));
    }
}
