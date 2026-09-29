<?php

namespace App\Http\Controllers;

use App\Enums\FinancialRuleType;
use App\Models\Family;
use App\Models\FinancialRule;
use Illuminate\Http\Request;

class FinancialRuleController extends Controller
{
    public function index()
    {
        $family = Family::with('financialRules')->firstOrFail();
        $rules = $family->financialRules()->orderBy('sort_order', 'asc')->get();

        return view('rules.index', compact('family', 'rules'));
    }

    public function update(Request $request, FinancialRule $rule)
    {
        $validated = $request->validate([
            'percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'apply_on_income' => 'nullable|boolean',
        ]);

        $rule->update([
            'percentage' => $validated['percentage'],
            'is_active' => $request->has('is_active'),
            'apply_on_income' => $request->has('apply_on_income'),
        ]);

        return back()->with('success', 'Regra financeira actualizada com sucesso!');
    }

    public function toggle(FinancialRule $rule)
    {
        $rule->update([
            'is_active' => !$rule->is_active,
        ]);

        $status = $rule->is_active ? 'activada' : 'desactivada';
        return back()->with('success', "Regra {$rule->name} {$status} com sucesso!");
    }
}
