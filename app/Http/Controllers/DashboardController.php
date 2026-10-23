<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Family;
use App\Models\Transaction;
use App\Services\BalanceService;
use App\Services\BudgetService;
use App\Services\DebtService;
use App\Services\FinancialHealthService;
use App\Services\NotificationService;
use App\Services\SavingsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected BalanceService $balanceService,
        protected BudgetService $budgetService,
        protected SavingsService $savingsService,
        protected DebtService $debtService,
        protected FinancialHealthService $financialHealthService,
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $family = $user?->family;

        if (!$family) {
            $family = Family::first() ?? Family::create([
                'name' => 'Família ' . ($user?->name ?? 'Filipe dos Santos'),
                'currency' => 'MZN',
                'locale' => 'pt_MZ',
                'timezone' => 'Africa/Maputo',
            ]);
            if ($user && !$user->family_id) {
                $user->family_id = $family->id;
                $user->save();
            }
        }

        $currency = $family->currency ?? 'MZN';

        $availableMonths = Transaction::when($family, function ($q) use ($family) {
            $q->whereHas('account', fn ($sq) => $sq->where('family_id', $family->id));
        })
            ->selectRaw("SUBSTR(transaction_date, 1, 7) as month_val")
            ->distinct()
            ->orderBy('month_val', 'desc')
            ->pluck('month_val')
            ->toArray();

        $currentMonth = $request->query('month');
        if (!$currentMonth || !in_array($currentMonth, $availableMonths)) {
            $currentMonth = !empty($availableMonths) ? $availableMonths[0] : Carbon::now()->format('Y-m');
        }

        if (!in_array($currentMonth, $availableMonths)) {
            array_unshift($availableMonths, $currentMonth);
        }

        // Net worth
        $netWorth = $this->balanceService->getFamilyNetWorth($family);

        // Monthly Totals (only including general accounts to avoid double-counting sub-account envelope movements)
        $monthlyIncome = (int) Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id)->where('include_in_total', true))
            ->where('type', TransactionType::Income)
            ->where('status', TransactionStatus::Completed)
            ->forMonth($currentMonth)
            ->sum('amount');

        $monthlyExpense = (int) Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id)->where('include_in_total', true))
            ->where('type', TransactionType::Expense)
            ->where('status', TransactionStatus::Completed)
            ->forMonth($currentMonth)
            ->sum('amount');

        $monthlyBalance = $monthlyIncome - $monthlyExpense;

        // Budgets
        $budgetOverview = $this->budgetService->getFamilyMonthlyBudgetOverview($family, $currentMonth);

        // Savings & Debts
        $savingsSummary = $this->savingsService->getFamilySavingsSummary($family);
        $rawGoals = \App\Models\SavingsGoal::where('family_id', $family->id)->get();
        $savingsGoals = $rawGoals->map(fn ($g) => $this->savingsService->getGoalProgress($g))->toArray();
        $debtSummary = $this->debtService->getFamilyDebtSummary($family);

        // Health Score & Alerts
        $healthReport = $this->financialHealthService->getDiagnosticReport($family, $currentMonth);
        $alerts = $this->notificationService->getActiveAlerts($family, $currentMonth);

        // Recent Transactions
        $recentTransactions = Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->with(['account', 'category', 'transferAccount'])
            ->latest('transaction_date')
            ->latest('id')
            ->take(8)
            ->get();

        return view('dashboard', [
            'family' => $family,
            'currency' => $currency,
            'currentMonth' => $currentMonth,
            'availableMonths' => $availableMonths,
            'netWorth' => $netWorth,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'monthlyBalance' => $monthlyBalance,
            'monthlyNet' => $monthlyBalance,
            'formattedMonthlyIncome' => formatMoney($monthlyIncome, $currency),
            'formattedMonthlyExpense' => formatMoney($monthlyExpense, $currency),
            'formattedMonthlyBalance' => formatMoney($monthlyBalance, $currency),
            'budgetOverview' => $budgetOverview,
            'savingsSummary' => $savingsSummary,
            'savingsGoals' => $savingsGoals,
            'debtSummary' => $debtSummary,
            'healthReport' => $healthReport,
            'alerts' => $alerts,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
