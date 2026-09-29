<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Family;
use App\Services\BalanceService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    public function index()
    {
        $family = Family::with('accounts')->firstOrFail();
        $accounts = $family->accounts()->get();
        $netWorth = $this->balanceService->getFamilyNetWorth($family);

        return view('accounts.index', compact('family', 'accounts', 'netWorth'));
    }

    public function store(Request $request)
    {
        $family = Family::firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'initial_balance' => 'required',
            'description' => 'nullable|string',
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
            'include_in_total' => 'nullable|boolean',
        ]);

        $initial = parseMoney($validated['initial_balance']);

        $account = Account::create([
            'family_id' => $family->id,
            'name' => $validated['name'],
            'type' => AccountType::from($validated['type']),
            'initial_balance' => $initial,
            'balance' => $initial,
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#3B82F6',
            'icon' => $validated['icon'] ?? '🏦',
            'is_active' => true,
            'include_in_total' => $request->has('include_in_total'),
        ]);

        return back()->with('success', 'Conta criada com sucesso!');
    }

    public function recalculate(Account $account)
    {
        $newBalance = $this->balanceService->recalculateAccountBalance($account);

        return back()->with('success', 'Saldo recalculado com sucesso: ' . formatMoney($newBalance));
    }
}
