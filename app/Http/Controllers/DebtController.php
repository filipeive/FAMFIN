<?php

namespace App\Http\Controllers;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Family;
use App\Services\DebtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DebtController extends Controller
{
    public function __construct(
        protected DebtService $debtService
    ) {}

    public function index(): View
    {
        $family = Family::first() ?? Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $summary = $this->debtService->getFamilyDebtSummary($family);
        $debts = Debt::where('family_id', $family->id)
            ->with(['payments' => fn ($q) => $q->latest('payment_date')->take(5)])
            ->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'overdue' THEN 2 ELSE 3 END")
            ->latest('created_at')
            ->get();

        $accounts = Account::where('family_id', $family->id)
            ->where('is_active', true)
            ->get();

        return view('debts.index', [
            'family' => $family,
            'summary' => $summary,
            'debts' => $debts,
            'accounts' => $accounts,
            'debtTypes' => DebtType::cases(),
            'debtStatuses' => DebtStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'creditor' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'type' => 'required|in:lent,borrowed',
            'original_amount' => 'required',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'monthly_payment' => 'nullable',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $family = Family::first();
        $originalCentavos = parseMoney($validated['original_amount']);
        $monthlyCentavos = !empty($validated['monthly_payment']) ? parseMoney($validated['monthly_payment']) : null;

        Debt::create([
            'family_id' => $family->id,
            'creditor' => $validated['creditor'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'original_amount' => $originalCentavos,
            'remaining_amount' => $originalCentavos,
            'monthly_payment' => $monthlyCentavos,
            'interest_rate' => $validated['interest_rate'] ?? 0,
            'start_date' => $validated['start_date'] ?? now()->toDateString(),
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => DebtStatus::Active,
        ]);

        return redirect()->route('debts.index')->with('success', 'Dívida registada com sucesso!');
    }

    public function edit(Debt $debt): View
    {
        return view('debts.edit', [
            'debt' => $debt,
            'debtTypes' => DebtType::cases(),
            'debtStatuses' => DebtStatus::cases(),
        ]);
    }

    public function update(Request $request, Debt $debt): RedirectResponse
    {
        $validated = $request->validate([
            'creditor' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'type' => 'required|in:lent,borrowed',
            'original_amount' => 'required',
            'remaining_amount' => 'required',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'monthly_payment' => 'nullable',
            'due_date' => 'nullable|date',
            'status' => 'required|in:active,paid,overdue,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $originalCentavos = parseMoney($validated['original_amount']);
        $remainingCentavos = parseMoney($validated['remaining_amount']);
        $monthlyCentavos = !empty($validated['monthly_payment']) ? parseMoney($validated['monthly_payment']) : null;

        $debt->update([
            'creditor' => $validated['creditor'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'original_amount' => $originalCentavos,
            'remaining_amount' => $remainingCentavos,
            'monthly_payment' => $monthlyCentavos,
            'interest_rate' => $validated['interest_rate'] ?? 0,
            'due_date' => $validated['due_date'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('debts.index')->with('success', 'Dívida atualizada com sucesso!');
    }

    public function destroy(Debt $debt): RedirectResponse
    {
        $this->debtService->deleteDebt($debt);
        return redirect()->route('debts.index')->with('success', 'Dívida eliminada com sucesso!');
    }

    public function payment(Request $request, Debt $debt): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required',
            'account_id' => 'nullable|exists:accounts,id',
            'payment_date' => 'nullable|date',
            'note' => 'nullable|string|max:500',
        ]);

        $amountInCentavos = parseMoney($validated['amount']);
        if ($amountInCentavos <= 0) {
            return back()->with('error', 'O valor da amortização deve ser superior a zero.');
        }

        $account = !empty($validated['account_id']) ? Account::find($validated['account_id']) : null;

        $this->debtService->recordPayment(
            $debt,
            $amountInCentavos,
            auth()->id(),
            $validated['payment_date'] ?? now()->toDateString(),
            $validated['note'] ?? null,
            $account
        );

        return redirect()->route('debts.index')->with('success', 'Amortização de dívida registada com sucesso!');
    }

    public function destroyPayment(DebtPayment $payment): RedirectResponse
    {
        $this->debtService->deletePayment($payment);
        return redirect()->back()->with('success', 'Pagamento/Amortização removido com sucesso!');
    }
}
