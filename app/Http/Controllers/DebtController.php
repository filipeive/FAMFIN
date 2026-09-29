<?php

namespace App\Http\Controllers;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Models\Debt;
use App\Models\Family;
use App\Services\DebtService;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    public function __construct(
        protected DebtService $debtService
    ) {}

    public function index()
    {
        $family = Family::with('debts.payments')->firstOrFail();
        $accounts = $family->accounts()->where('is_active', true)->get();

        $summary = $this->debtService->getFamilyDebtSummary($family);
        $debts = $family->debts()->orderBy('created_at', 'desc')->get();

        return view('debts.index', compact('family', 'debts', 'summary', 'accounts'));
    }

    public function store(Request $request)
    {
        $family = Family::firstOrFail();

        $validated = $request->validate([
            'person_name' => 'required|string|max:255',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'original_amount' => 'required',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
        ]);

        $amount = parseMoney($validated['original_amount']);

        Debt::create([
            'family_id' => $family->id,
            'person_name' => $validated['person_name'],
            'type' => DebtType::from($validated['type']),
            'description' => $validated['description'] ?? "Dívida com {$validated['person_name']}",
            'original_amount' => $amount,
            'remaining_amount' => $amount,
            'start_date' => $validated['start_date'] ?? now()->toDateString(),
            'due_date' => $validated['due_date'] ?? null,
            'status' => DebtStatus::Active,
        ]);

        return back()->with('success', 'Registo de dívida criado com sucesso!');
    }

    public function pay(Request $request, Debt $debt)
    {
        $family = Family::firstOrFail();

        $validated = $request->validate([
            'amount' => 'required',
            'account_id' => 'nullable|exists:accounts,id',
            'payment_date' => 'nullable|date',
            'note' => 'nullable|string|max:255',
        ]);

        $amount = parseMoney($validated['amount']);
        $account = !empty($validated['account_id']) ? $family->accounts()->find($validated['account_id']) : null;

        $this->debtService->recordPayment(
            $debt,
            $amount,
            $family->users()->first()?->id,
            $validated['payment_date'] ?? now()->toDateString(),
            $validated['note'] ?? null,
            $account
        );

        return back()->with('success', 'Pagamento de dívida registado com sucesso!');
    }
}
