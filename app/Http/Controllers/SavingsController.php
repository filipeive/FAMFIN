<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\SavingsGoal;
use App\Services\SavingsService;
use Illuminate\Http\Request;

class SavingsController extends Controller
{
    public function __construct(
        protected SavingsService $savingsService
    ) {}

    public function index()
    {
        $family = Family::with('savingsGoals.contributions')->firstOrFail();
        $accounts = $family->accounts()->where('is_active', true)->get();

        $goalsWithProgress = $family->savingsGoals->map(function ($goal) {
            return array_merge(
                $goal->toArray(),
                $this->savingsService->getGoalProgress($goal)
            );
        });

        return view('savings.index', compact('family', 'goalsWithProgress', 'accounts'));
    }

    public function store(Request $request)
    {
        $family = Family::firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_amount' => 'required',
            'deadline' => 'nullable|date',
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);

        $validated['family_id'] = $family->id;
        $validated['target_amount'] = parseMoney($validated['target_amount']);
        $validated['current_amount'] = 0;

        SavingsGoal::create($validated);

        return back()->with('success', 'Meta de poupança criada com sucesso!');
    }

    public function contribute(Request $request, SavingsGoal $goal)
    {
        $family = Family::firstOrFail();

        $validated = $request->validate([
            'amount' => 'required',
            'account_id' => 'nullable|exists:accounts,id',
            'contribution_date' => 'nullable|date',
            'note' => 'nullable|string|max:255',
        ]);

        $amount = parseMoney($validated['amount']);
        $account = !empty($validated['account_id']) ? $family->accounts()->find($validated['account_id']) : null;

        $this->savingsService->addContribution(
            $goal,
            $amount,
            $family->users()->first()?->id,
            $validated['contribution_date'] ?? now()->toDateString(),
            $validated['note'] ?? null,
            $account
        );

        return back()->with('success', 'Contribuição adicionada com sucesso!');
    }
}
