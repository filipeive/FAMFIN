<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    public function index(Request $request)
    {
        $family = Family::firstOrFail();

        $month = $request->get('month');
        $type = $request->get('type');
        $categoryId = $request->get('category_id');
        $accountId = $request->get('account_id');
        $search = $request->get('search');

        $query = Transaction::with(['category', 'account', 'transferAccount'])
            ->whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($month) {
            $query->forMonth($month);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        if ($search) {
            $query->where('description', 'like', "%{$search}%");
        }

        $transactions = $query->paginate(20)->withQueryString();

        $accounts = $family->accounts()->get();
        $categories = $family->categories()->get();

        $availableMonths = Transaction::selectRaw("DISTINCT SUBSTR(transaction_date, 1, 7) as m")
            ->orderBy('m', 'desc')
            ->pluck('m')
            ->filter()
            ->toArray();

        return view('transactions.index', compact(
            'family',
            'transactions',
            'accounts',
            'categories',
            'availableMonths',
            'month',
            'type',
            'categoryId',
            'accountId',
            'search'
        ));
    }

    public function create()
    {
        $family = Family::with(['accounts', 'categories'])->firstOrFail();
        return view('transactions.create', compact('family'));
    }

    public function store(Request $request)
    {
        $family = Family::firstOrFail();

        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'transfer_account_id' => 'nullable|exists:accounts,id',
            'type' => 'required|string',
            'amount' => 'required',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $validated['amount'] = parseMoney($validated['amount']);

        $this->transactionService->createTransaction($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transacção registada com sucesso!');
    }

    public function destroy(Transaction $transaction)
    {
        $this->transactionService->deleteTransaction($transaction);

        return back()->with('success', 'Transacção eliminada com sucesso!');
    }
}
