<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Family;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(
        protected BudgetService $budgetService
    ) {}

    public function index(Request $request): View
    {
        $family = Family::first() ?? Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $month = $request->query('month', Carbon::now()->format('Y-m'));

        $overview = $this->budgetService->getFamilyMonthlyBudgetOverview($family, $month);
        $categories = Category::where('family_id', $family->id)
            ->where('type', 'expense')
            ->orderBy('name')
            ->get();

        return view('budgets.index', [
            'family' => $family,
            'currentMonth' => $month,
            'overview' => $overview,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'month' => 'required|regex:/^\d{4}-\d{2}$/',
            'amount' => 'required',
            'alert_threshold' => 'nullable|integer|min:1|max:100',
        ]);

        $family = Family::first();
        $amountInCentavos = parseMoney($validated['amount']);
        $threshold = (int) ($validated['alert_threshold'] ?? 80);

        $this->budgetService->setBudget(
            $family,
            $validated['category_id'],
            $validated['month'],
            $amountInCentavos,
            $threshold
        );

        return redirect()->route('budgets.index', ['month' => $validated['month']])
            ->with('success', 'Orçamento guardado com sucesso!');
    }

    public function copy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_month' => 'required|regex:/^\d{4}-\d{2}$/',
            'to_month' => 'required|regex:/^\d{4}-\d{2}$/',
        ]);

        $family = Family::first();
        $count = $this->budgetService->copyBudgetsToNextMonth(
            $family,
            $validated['from_month'],
            $validated['to_month']
        );

        return redirect()->route('budgets.index', ['month' => $validated['to_month']])
            ->with('success', "{$count} orçamentos copiados de {$validated['from_month']} para {$validated['to_month']}!");
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $month = $budget->month;
        $budget->delete();

        return redirect()->route('budgets.index', ['month' => $month])
            ->with('success', 'Orçamento eliminado com sucesso!');
    }
}
