<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    public function index(Request $request): View
    {
        $family = auth()->user()?->family ?? Family::first() ?? Family::create([
            'name' => 'Família dos Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
        ]);
        $familyId = $family ? $family->id : null;

        $query = Transaction::with(['account', 'category', 'transferAccount'])
            ->when($familyId, fn ($q) => $q->whereHas('account', fn ($sq) => $sq->where('family_id', $familyId)));

        if ($year = $request->query('year')) {
            $query->whereYear('transaction_date', $year);
        }

        if ($month = $request->query('month')) {
            $query->forMonth($month);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($accountId = $request->query('account_id')) {
            $query->where(function ($q) use ($accountId) {
                $q->where('account_id', $accountId)
                  ->orWhere('transfer_account_id', $accountId);
            });
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest('transaction_date')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $accounts = Account::when($familyId, fn ($q) => $q->where('family_id', $familyId))->where('is_active', true)->get();
        $categories = Category::when($familyId, fn ($q) => $q->where('family_id', $familyId))->orderBy('name')->get();

        $months = Transaction::when($familyId, fn ($q) => $q->whereHas('account', fn ($sq) => $sq->where('family_id', $familyId)))
            ->selectRaw("SUBSTR(transaction_date, 1, 7) as month_val")
            ->distinct()
            ->orderBy('month_val', 'desc')
            ->pluck('month_val')
            ->toArray();

        $years = Transaction::when($familyId, fn ($q) => $q->whereHas('account', fn ($sq) => $sq->where('family_id', $familyId)))
            ->selectRaw("SUBSTR(transaction_date, 1, 4) as year_val")
            ->distinct()
            ->orderBy('year_val', 'desc')
            ->pluck('year_val')
            ->toArray();

        return view('transactions.index', [
            'family' => $family,
            'transactions' => $transactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'availableMonths' => $months,
            'availableYears' => $years,
            'filters' => $request->all(),
        ]);
    }

    public function create(): View
    {
        $family = auth()->user()?->family ?? Family::first();
        $familyId = $family ? $family->id : null;

        $accounts = Account::when($familyId, fn ($q) => $q->where('family_id', $familyId))->where('is_active', true)->get();
        $categories = Category::when($familyId, fn ($q) => $q->where('family_id', $familyId))->orderBy('name')->get();

        return view('transactions.create', [
            'accounts' => $accounts,
            'categories' => $categories,
            'types' => TransactionType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required',
            'description' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'transfer_account_id' => 'nullable|required_if:type,transfer|exists:accounts,id',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $amountInCentavos = parseMoney($validated['amount']);

        $this->transactionService->createTransaction([
            'account_id' => $validated['account_id'],
            'type' => $validated['type'],
            'amount' => $amountInCentavos,
            'description' => $validated['description'],
            'category_id' => $validated['category_id'] ?? null,
            'transfer_account_id' => $validated['transfer_account_id'] ?? null,
            'transaction_date' => $validated['transaction_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => TransactionStatus::Completed,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transação registada com sucesso!');
    }

    public function edit(Transaction $transaction): View
    {
        $family = auth()->user()?->family ?? Family::first();
        $familyId = $family ? $family->id : null;

        $accounts = Account::when($familyId, fn ($q) => $q->where('family_id', $familyId))->where('is_active', true)->get();
        $categories = Category::when($familyId, fn ($q) => $q->where('family_id', $familyId))->orderBy('name')->get();

        return view('transactions.edit', [
            'transaction' => $transaction,
            'accounts' => $accounts,
            'categories' => $categories,
            'types' => TransactionType::cases(),
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required',
            'description' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'transfer_account_id' => 'nullable|required_if:type,transfer|exists:accounts,id',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $amountInCentavos = parseMoney($validated['amount']);

        $this->transactionService->updateTransaction($transaction, [
            'account_id' => $validated['account_id'],
            'type' => $validated['type'],
            'amount' => $amountInCentavos,
            'description' => $validated['description'],
            'category_id' => $validated['category_id'] ?? null,
            'transfer_account_id' => $validated['transfer_account_id'] ?? null,
            'transaction_date' => $validated['transaction_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transação atualizada com sucesso!');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->transactionService->deleteTransaction($transaction);
        return redirect()->back()->with('success', 'Transação eliminada com sucesso!');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'exists:transactions,id',
        ]);

        $count = $this->transactionService->bulkDeleteTransactions($validated['transaction_ids']);

        return redirect()->back()->with('success', "{$count} transações eliminadas com sucesso!");
    }
}
