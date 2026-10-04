<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Family;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use App\Services\SavingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingsController extends Controller
{
    public function __construct(
        protected SavingsService $savingsService
    ) {}

    public function index(): View
    {
        $family = Family::first() ?? Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $goals = SavingsGoal::where('family_id', $family->id)
            ->where('is_active', true)
            ->with(['contributions' => fn ($q) => $q->latest('contribution_date')->take(5)])
            ->get()
            ->map(function ($goal) {
                $progress = $this->savingsService->getGoalProgress($goal);
                $progress['model'] = $goal;
                return $progress;
            });

        $accounts = Account::where('family_id', $family->id)
            ->where('is_active', true)
            ->get();

        return view('savings.index', [
            'family' => $family,
            'goals' => $goals,
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required',
            'deadline' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $family = Family::first();
        $targetInCentavos = parseMoney($validated['target_amount']);

        SavingsGoal::create([
            'family_id' => $family->id,
            'name' => $validated['name'],
            'target_amount' => $targetInCentavos,
            'current_amount' => 0,
            'deadline' => $validated['deadline'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('savings.index')->with('success', 'Meta de poupança criada com sucesso!');
    }

    public function edit(SavingsGoal $goal): View
    {
        return view('savings.edit', [
            'goal' => $goal,
        ]);
    }

    public function update(Request $request, SavingsGoal $goal): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required',
            'deadline' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $targetInCentavos = parseMoney($validated['target_amount']);

        $goal->update([
            'name' => $validated['name'],
            'target_amount' => $targetInCentavos,
            'deadline' => $validated['deadline'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('savings.index')->with('success', 'Meta de poupança atualizada com sucesso!');
    }

    public function destroy(SavingsGoal $goal): RedirectResponse
    {
        $this->savingsService->deleteGoal($goal);
        return redirect()->route('savings.index')->with('success', 'Meta de poupança eliminada com sucesso!');
    }

    public function contribute(Request $request, SavingsGoal $goal): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required',
            'account_id' => 'nullable|exists:accounts,id',
            'contribution_date' => 'nullable|date',
            'note' => 'nullable|string|max:500',
        ]);

        $amountInCentavos = parseMoney($validated['amount']);
        $account = !empty($validated['account_id']) ? Account::find($validated['account_id']) : null;

        $this->savingsService->addContribution(
            $goal,
            $amountInCentavos,
            null,
            $validated['contribution_date'] ?? now()->toDateString(),
            $validated['note'] ?? null,
            $account
        );

        return redirect()->route('savings.index')->with('success', 'Contribuição registada com sucesso!');
    }

    public function destroyContribution(SavingsContribution $contribution): RedirectResponse
    {
        $this->savingsService->deleteContribution($contribution);
        return redirect()->back()->with('success', 'Aporte removido com sucesso!');
    }
}
