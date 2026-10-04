<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Family;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    /**
     * Get monthly cash flow trend for the last N months.
     *
     * @param Family $family
     * @param int $monthsCount
     * @return array
     */
    public function getMonthlyCashFlowTrend(Family $family, int $monthsCount = 12): array
    {
        $months = Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->selectRaw("SUBSTR(transaction_date, 1, 7) as month_val")
            ->distinct()
            ->orderBy('month_val', 'desc')
            ->take($monthsCount)
            ->pluck('month_val')
            ->reverse()
            ->values()
            ->toArray();

        if (empty($months)) {
            $months = [Carbon::now()->format('Y-m')];
        }

        $items = [];
        $maxVal = 1;

        foreach ($months as $month) {
            $incomes = (int) Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id))
                ->where('type', TransactionType::Income)
                ->where('status', TransactionStatus::Completed)
                ->forMonth($month)
                ->sum('amount');

            $expenses = (int) Transaction::whereHas('account', fn ($q) => $q->where('family_id', $family->id))
                ->where('type', TransactionType::Expense)
                ->where('status', TransactionStatus::Completed)
                ->forMonth($month)
                ->sum('amount');

            $net = $incomes - $expenses;
            $savingsRate = $incomes > 0 ? max(0, round(($net / $incomes) * 100, 1)) : 0.0;

            if ($incomes > $maxVal) $maxVal = $incomes;
            if ($expenses > $maxVal) $maxVal = $expenses;

            $items[] = [
                'month' => $month,
                'label' => Carbon::parse($month . '-01')->translatedFormat('M y'),
                'income' => $incomes,
                'expense' => $expenses,
                'net' => $net,
                'savings_rate' => $savingsRate,
                'formatted_income' => formatMoney($incomes, $family->currency),
                'formatted_expense' => formatMoney($expenses, $family->currency),
                'formatted_net' => formatMoney($net, $family->currency),
            ];
        }

        return [
            'items' => $items,
            'max_value' => $maxVal,
            'months_count' => count($items),
        ];
    }

    /**
     * Get expense breakdown by category for a specific month (or all-time if null).
     *
     * @param Family $family
     * @param string|null $month
     * @return array
     */
    public function getCategoryExpenseBreakdown(Family $family, ?string $month = null): array
    {
        $query = Transaction::with('category')
            ->whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->where('type', TransactionType::Expense)
            ->where('status', TransactionStatus::Completed);

        if ($month) {
            $query->forMonth($month);
        }

        $transactions = $query->get();
        $totalExpenses = (int) $transactions->sum('amount');

        $byCategory = $transactions->groupBy('category_id')->map(function ($items, $catId) use ($totalExpenses, $family) {
            $category = $items->first()->category;
            $amount = (int) $items->sum('amount');
            $percentage = $totalExpenses > 0 ? round(($amount / $totalExpenses) * 100, 1) : 0.0;

            return [
                'category_id' => $catId,
                'name' => $category?->name ?? 'Sem Categoria',
                'color' => $category?->color ?? '#64748B',
                'amount' => $amount,
                'percentage' => $percentage,
                'formatted_amount' => formatMoney($amount, $family->currency),
            ];
        })->sortByDesc('amount')->values()->toArray();

        return [
            'categories' => $byCategory,
            'total_expenses' => $totalExpenses,
            'formatted_total_expenses' => formatMoney($totalExpenses, $family->currency),
        ];
    }

    /**
     * Stream CSV export of all transactions.
     *
     * @param Family $family
     * @param string|null $month
     * @return StreamedResponse
     */
    public function exportTransactionsCsv(Family $family, ?string $month = null): StreamedResponse
    {
        $fileName = 'transacoes_' . ($month ?: 'historico') . '.csv';

        $query = Transaction::with(['account', 'category', 'transferAccount'])
            ->whereHas('account', fn ($q) => $q->where('family_id', $family->id))
            ->latest('transaction_date');

        if ($month) {
            $query->forMonth($month);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query, $family) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['ID', 'Data', 'Tipo', 'Conta', 'Destino', 'Categoria', 'Descrição', 'Valor (MZN)', 'Notas']);

            $query->chunk(200, function ($transactions) use ($handle, $family) {
                foreach ($transactions as $tx) {
                    fputcsv($handle, [
                        $tx->id,
                        Carbon::parse($tx->transaction_date)->format('d/m/Y'),
                        ucfirst($tx->type->value),
                        $tx->account->name ?? '—',
                        $tx->transferAccount->name ?? '—',
                        $tx->category->name ?? 'Sem categoria',
                        $tx->description,
                        number_format($tx->amount / 100, 2, ',', '.'),
                        $tx->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
