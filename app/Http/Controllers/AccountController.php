<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Family;
use App\Services\BalanceService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        protected BalanceService $balanceService,
        protected TransactionService $transactionService
    ) {}

    public function index(): View
    {
        $family = Family::first() ?? Family::create([
            'name' => 'Família Filipe dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $defaultParent = Account::where('family_id', $family->id)
            ->whereNull('parent_id')
            ->where('is_allowance', false)
            ->first();

        // Ensure default sub-accounts for Ayume and Eunice exist with 5.000 MT allowance
        $this->ensureDefaultAllowancesExist($family, $defaultParent);

        $allAccounts = Account::where('family_id', $family->id)
            ->withCount('transactions')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        $mainAccounts = $allAccounts->where('is_allowance', false);
        $allowanceAccounts = $allAccounts->where('is_allowance', true);

        $totalBalance = (int) $mainAccounts->where('is_active', true)->where('include_in_total', true)->sum('balance');

        return view('accounts.index', [
            'family' => $family,
            'accounts' => $mainAccounts,
            'allowanceAccounts' => $allowanceAccounts,
            'allAccounts' => $allAccounts,
            'totalBalance' => $totalBalance,
            'formattedTotalBalance' => formatMoney($totalBalance, $family->currency),
            'accountTypes' => AccountType::cases(),
        ]);
    }

    public function show(Account $account): View
    {
        $account->loadMissing(['family', 'parent']);
        $transactions = $account->transactions()
            ->with(['category', 'transferAccount', 'user'])
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
            'type' => 'required|in:checking,savings,cash,investment,credit_card,mobile_money,sub_account,other',
            'parent_id' => 'nullable|exists:accounts,id',
            'initial_balance' => 'nullable',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'include_in_total' => 'nullable|boolean',
            'is_allowance' => 'nullable|boolean',
            'assigned_to_name' => 'nullable|string|max:255',
            'allowance_monthly_amount' => 'nullable',
        ]);

        $family = Family::first();
        $initialBalanceCentavos = !empty($validated['initial_balance']) ? parseMoney($validated['initial_balance']) : 0;
        $allowanceAmountCentavos = !empty($validated['allowance_monthly_amount']) ? parseMoney($validated['allowance_monthly_amount']) : null;
        $isAllowance = $request->boolean('is_allowance', false);

        Account::create([
            'family_id' => $family->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'currency' => $family->currency ?? 'MZN',
            'initial_balance' => $initialBalanceCentavos,
            'balance' => $initialBalanceCentavos,
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'include_in_total' => $isAllowance ? false : $request->boolean('include_in_total', true),
            'is_allowance' => $isAllowance,
            'assigned_to_name' => $validated['assigned_to_name'] ?? null,
            'allowance_monthly_amount' => $allowanceAmountCentavos,
            'is_active' => true,
        ]);

        return redirect()->route('accounts.index')->with('success', 'Conta criada com sucesso!');
    }

    public function edit(Account $account): View
    {
        $family = Family::first();
        $parentAccounts = Account::where('family_id', $family->id)
            ->where('id', '!=', $account->id)
            ->where('is_allowance', false)
            ->get();

        return view('accounts.edit', [
            'account' => $account,
            'accountTypes' => AccountType::cases(),
            'parentAccounts' => $parentAccounts,
        ]);
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:checking,savings,cash,investment,credit_card,mobile_money,sub_account,other',
            'parent_id' => 'nullable|exists:accounts,id',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'include_in_total' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_allowance' => 'nullable|boolean',
            'assigned_to_name' => 'nullable|string|max:255',
            'allowance_monthly_amount' => 'nullable',
        ]);

        $allowanceAmountCentavos = !empty($validated['allowance_monthly_amount']) ? parseMoney($validated['allowance_monthly_amount']) : null;
        $isAllowance = $request->boolean('is_allowance', $account->is_allowance);

        $account->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'] ?? $account->parent_id,
            'bank_name' => $validated['bank_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'include_in_total' => $isAllowance ? false : $request->boolean('include_in_total', true),
            'is_active' => $request->boolean('is_active', true),
            'is_allowance' => $isAllowance,
            'assigned_to_name' => $validated['assigned_to_name'] ?? $account->assigned_to_name,
            'allowance_monthly_amount' => $allowanceAmountCentavos ?? $account->allowance_monthly_amount,
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

    public function allocateAllowance(Request $request, Account $account): RedirectResponse
    {
        $validated = $request->validate([
            'source_account_id' => 'required|exists:accounts,id',
            'amount' => 'required',
            'allocation_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $sourceAccount = Account::findOrFail($validated['source_account_id']);
        $amountInCentavos = parseMoney($validated['amount']);

        if ($amountInCentavos <= 0) {
            return back()->with('error', 'O valor da mesada deve ser superior a zero.');
        }

        $this->transactionService->allocateAllowance(
            $account,
            $sourceAccount,
            $amountInCentavos,
            $validated['allocation_date'] ?? now()->toDateString(),
            $validated['notes'] ?? null,
            auth()->id()
        );

        return redirect()->route('accounts.index')->with('success', "Mesada atribuída com sucesso a {$account->name}!");
    }

    protected function ensureDefaultAllowancesExist(Family $family, ?Account $parent): void
    {
        $daughters = [
            [
                'name' => 'Mesada - Ayume',
                'assigned_to_name' => 'Ayume',
                'color' => '#EC4899',
                'icon' => '👧',
            ],
            [
                'name' => 'Mesada - Eunice',
                'assigned_to_name' => 'Eunice',
                'color' => '#8B5CF6',
                'icon' => '👧',
            ],
        ];

        foreach ($daughters as $d) {
            $exists = Account::where('family_id', $family->id)
                ->where(function ($q) use ($d) {
                    $q->where('assigned_to_name', $d['assigned_to_name'])
                      ->orWhere('name', $d['name']);
                })->first();

            if (!$exists) {
                Account::create([
                    'family_id' => $family->id,
                    'parent_id' => $parent?->id,
                    'name' => $d['name'],
                    'type' => AccountType::SubAccount,
                    'currency' => $family->currency ?? 'MZN',
                    'balance' => 0,
                    'initial_balance' => 0,
                    'description' => "Subconta e envelope de mesada mensal para {$d['assigned_to_name']}",
                    'color' => $d['color'],
                    'icon' => $d['icon'],
                    'is_active' => true,
                    'include_in_total' => false,
                    'is_allowance' => true,
                    'assigned_to_name' => $d['assigned_to_name'],
                    'allowance_monthly_amount' => 500000, // 5.000,00 MT
                ]);
            }
        }
    }
}
