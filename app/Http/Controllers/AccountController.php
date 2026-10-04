<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Family;
use App\Services\BalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    public function index(): View
    {
        $family = Family::first() ?? Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $accounts = Account::where('family_id', $family->id)
            ->withCount('transactions')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        $totalBalance = (int) $accounts->where('is_active', true)->where('include_in_total', true)->sum('balance');

        return view('accounts.index', [
            'family' => $family,
            'accounts' => $accounts,
            'totalBalance' => $totalBalance,
            'formattedTotalBalance' => formatMoney($totalBalance, $family->currency),
            'accountTypes' => AccountType::cases(),
        ]);
    }

    public function show(Account $account): View
    {
        $account->loadMissing('family');
        $transactions = $account->transactions()
            ->with(['category', 'transferAccount'])
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(30);

        return view('accounts.show', [
            'account' => $account,
            'transactions' => $transactions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:checking,savings,cash,investment,credit_card,mobile_money,other',
            'initial_balance' => 'nullable',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'include_in_total' => 'nullable|boolean',
        ]);

        $family = Family::first();
        $initialBalanceCentavos = !empty($validated['initial_balance']) ? parseMoney($validated['initial_balance']) : 0;

        Account::create([
            'family_id' => $family->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'currency' => $family->currency ?? 'MZN',
            'initial_balance' => $initialBalanceCentavos,
            'balance' => $initialBalanceCentavos,
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'include_in_total' => $request->boolean('include_in_total', true),
            'is_active' => true,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Conta criada com sucesso!');
    }

    public function edit(Account $account): View
    {
        return view('accounts.edit', [
            'account' => $account,
            'accountTypes' => AccountType::cases(),
        ]);
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:checking,savings,cash,investment,credit_card,mobile_money,other',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'include_in_total' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $account->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'include_in_total' => $request->boolean('include_in_total', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('accounts.index')->with('success', 'Conta atualizada com sucesso!');
    }

    public function destroy(Account $account): RedirectResponse
    {
        if ($account->transactions()->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'Não é possível eliminar uma conta com transações associadas. Pode desativá-la nas definições.',
            ]);
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('success', 'Conta eliminada com sucesso!');
    }

    public function recalculate(Request $request): RedirectResponse
    {
        $family = Family::first();
        if ($family) {
            $this->balanceService->recalculateFamilyAccounts($family);
        }

        return redirect()->route('accounts.index')->with('success', 'Saldos de todas as contas recalculados com sucesso!');
    }
}
