<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Transaction;
use App\Services\BalanceService;
use App\Services\DebtService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected BalanceService $balanceService,
        protected DebtService $debtService
    ) {}

    public function index(Request $request): View
    {
        $family = Family::first() ?? Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $months = Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->selectRaw("SUBSTR(transaction_date, 1, 7) as month_val")
            ->distinct()
            ->orderBy('month_val', 'desc')
            ->pluck('month_val')
            ->toArray();

        if ($request->has('month')) {
            $selectedMonth = $request->query('month') ?: null;
        } else {
            $selectedMonth = !empty($months) ? $months[0] : Carbon::now()->format('Y-m');
        }

        $trend = $this->reportService->getMonthlyCashFlowTrend($family, 12);
        $categoryBreakdown = $this->reportService->getCategoryExpenseBreakdown($family, $selectedMonth);
        $netWorth = $this->balanceService->getFamilyNetWorth($family);
        $debtSummary = $this->debtService->getFamilyDebtSummary($family);

        return view('reports.index', [
            'family' => $family,
            'selectedMonth' => $selectedMonth,
            'availableMonths' => $months,
            'trend' => $trend,
            'categoryBreakdown' => $categoryBreakdown,
            'netWorth' => $netWorth,
            'debtSummary' => $debtSummary,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $family = Family::first() ?? Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);
        $month = $request->query('month');

        return $this->reportService->exportTransactionsCsv($family, $month);
    }
}
