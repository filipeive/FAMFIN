<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BudgetController extends Controller
{
    public function __construct(
        protected BudgetService $budgetService
    ) {}

    public function index(Request $request)
    {
        $family = Family::firstOrFail();

        $latestTransDate = Transaction::latest('transaction_date')->value('transaction_date');
        $defaultMonth = $latestTransDate ? Carbon::parse($latestTransDate)->format('Y-m') : now()->format('Y-m');
        $month = $request->get('month', $defaultMonth);

        $overview = $this->budgetService->getFamilyMonthlyBudgetOverview($family, $month);

        $categories = $family->categories()
            ->where('type', \App\Enums\CategoryType::Expense)
            ->get();

        $availableMonths = Transaction::selectRaw("DISTINCT SUBSTR(transaction_date, 1, 7) as m")
            ->orderBy('m', 'desc')
            ->pluck('m')
            ->filter()
            ->toArray();

        return view('budgets.index', compact('family', 'month', 'overview', 'categories', 'availableMonths'));
    }

    public function store(Request $request)
    {
        $family = Family::firstOrFail();

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'month' => 'required|string',
            'amount' => 'required',
            'alert_threshold' => 'nullable|integer|min:1|max:100',
        ]);

        $amount = parseMoney($validated['amount']);
        $threshold = (int) ($validated['alert_threshold'] ?? 80);

        $this->budgetService->setBudget($family, $validated['category_id'], $validated['month'], $amount, $threshold);

        return back()->with('success', 'Orçamento definido com sucesso!');
    }

    public function copy(Request $request)
    {
        $family = Family::firstOrFail();

        $validated = $request->validate([
            'from_month' => 'required|string',
            'to_month' => 'required|string',
        ]);

        $count = $this->budgetService->copyBudgetsToNextMonth($family, $validated['from_month'], $validated['to_month']);

        return redirect()->route('budgets.index', ['month' => $validated['to_month']])
            ->with('success', "{$count} orçamentos copiados para o mês {$validated['to_month']}!");
    }
}
