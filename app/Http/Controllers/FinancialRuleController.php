<?php

namespace App\Http\Controllers;

use App\Enums\FinancialRuleType;
use App\Models\Family;
use App\Models\FinancialRule;
use App\Services\FinancialRuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialRuleController extends Controller
{
    public function __construct(
        protected FinancialRuleService $financialRuleService
    ) {}

    public function index(Request $request): View
    {
        $family = Family::first() ?? Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $rules = FinancialRule::where('family_id', $family->id)
            ->orderBy('sort_order')
            ->get();

        $simulatedBreakdown = null;
        $simulatedGross = $request->query('simulate_gross');
        if ($simulatedGross) {
            $grossCentavos = parseMoney($simulatedGross);
            $simulatedBreakdown = $this->financialRuleService->calculateBreakdown($family, $grossCentavos);
        }

        return view('rules.index', [
            'family' => $family,
            'rules' => $rules,
            'ruleTypes' => FinancialRuleType::cases(),
            'simulatedBreakdown' => $simulatedBreakdown,
            'simulatedGross' => $simulatedGross,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:tithe,inss,yield,custom',
            'percentage' => 'required|numeric|min:0|max:100',
            'apply_on_income' => 'nullable|boolean',
            'base' => 'nullable|in:gross,net_after_inss',
        ]);

        $family = Family::first();

        FinancialRule::create([
            'family_id' => $family->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'percentage' => $validated['percentage'],
            'apply_on_income' => $request->boolean('apply_on_income', true),
            'config' => [
                'base' => $validated['base'] ?? 'gross',
            ],
            'is_active' => true,
            'sort_order' => FinancialRule::where('family_id', $family->id)->count() + 1,
        ]);

        return redirect()->route('rules.index')->with('success', 'Regra financeira adicionada com sucesso!');
    }

    public function toggle(FinancialRule $rule): RedirectResponse
    {
        $rule->update(['is_active' => !$rule->is_active]);
        return redirect()->route('rules.index')->with('success', 'Estado da regra atualizado com sucesso!');
    }

    public function destroy(FinancialRule $rule): RedirectResponse
    {
        $rule->delete();
        return redirect()->route('rules.index')->with('success', 'Regra financeira eliminada com sucesso!');
    }
}
