@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white flex items-center gap-2">
                💸 Transacções
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Histórico completo de receitas, despesas e transferências</p>
        </div>

        <a href="{{ route('transactions.create') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition shadow-lg shadow-emerald-950 flex items-center gap-1.5">
            + Registar Transacção
        </a>
    </div>

    <!-- Filters Bar -->
    <form method="GET" action="{{ route('transactions.index') }}" class="bg-slate-900 p-4 rounded-2xl border border-slate-800 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
        <!-- Month Filter -->
        <div>
            <label class="block text-[11px] font-semibold text-slate-400 mb-1">Mês</label>
            <select name="month" class="w-full bg-slate-950 text-xs text-slate-200 rounded-lg p-2 border border-slate-800 focus:outline-none focus:border-emerald-500">
                <option value="">Todos os meses</option>
                @foreach($availableMonths as $m)
                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        <!-- Type Filter -->
        <div>
            <label class="block text-[11px] font-semibold text-slate-400 mb-1">Tipo</label>
            <select name="type" class="w-full bg-slate-950 text-xs text-slate-200 rounded-lg p-2 border border-slate-800 focus:outline-none focus:border-emerald-500">
                <option value="">Todos os tipos</option>
                <option value="income" {{ $type === 'income' ? 'selected' : '' }}>Receita</option>
                <option value="expense" {{ $type === 'expense' ? 'selected' : '' }}>Despesa</option>
                <option value="transfer" {{ $type === 'transfer' ? 'selected' : '' }}>Transferência</option>
            </select>
        </div>

        <!-- Category Filter -->
        <div>
            <label class="block text-[11px] font-semibold text-slate-400 mb-1">Categoria</label>
            <select name="category_id" class="w-full bg-slate-950 text-xs text-slate-200 rounded-lg p-2 border border-slate-800 focus:outline-none focus:border-emerald-500">
                <option value="">Todas as categorias</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId === $cat->id ? 'selected' : '' }}>
                        {{ $cat->icon }} {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Account Filter -->
        <div>
            <label class="block text-[11px] font-semibold text-slate-400 mb-1">Conta</label>
            <select name="account_id" class="w-full bg-slate-950 text-xs text-slate-200 rounded-lg p-2 border border-slate-800 focus:outline-none focus:border-emerald-500">
                <option value="">Todas as contas</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ $accountId === $acc->id ? 'selected' : '' }}>
                        {{ $acc->icon }} {{ $acc->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Submit & Clear Button -->
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs p-2 rounded-lg transition">
                Filtrar
            </button>
            <a href="{{ route('transactions.index') }}" class="px-3 py-2 bg-slate-950 hover:bg-slate-800 text-slate-400 text-xs rounded-lg transition border border-slate-800">
                Limpar
            </a>
        </div>
    </form>

    <!-- Transactions Table -->
    <div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/60 text-slate-400 font-semibold uppercase tracking-wider text-[10px] border-b border-slate-800">
                    <tr>
                        <th class="p-3.5">Data</th>
                        <th class="p-3.5">Descrição</th>
                        <th class="p-3.5">Categoria</th>
                        <th class="p-3.5">Conta</th>
                        <th class="p-3.5 text-right">Valor</th>
                        <th class="p-3.5 text-center">Acções</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-3.5 text-slate-400 font-mono">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                            <td class="p-3.5">
                                <span class="font-semibold text-white">{{ $tx->description }}</span>
                                @if($tx->notes)
                                    <span class="block text-[10px] text-slate-400">{{ $tx->notes }}</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                @if($tx->category)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-800 text-slate-300">
                                        <span>{{ $tx->category->icon ?? '🏷️' }}</span>
                                        <span>{{ $tx->category->name }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                <span class="text-slate-300">{{ $tx->account->name }}</span>
                                @if($tx->is_transfer && $tx->transferAccount)
                                    <span class="text-sky-400"> → {{ $tx->transferAccount->name }}</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right font-bold font-mono">
                                <span class="{{ $tx->is_income ? 'text-emerald-400' : ($tx->is_transfer ? 'text-sky-400' : 'text-slate-200') }}">
                                    {{ $tx->is_income ? '+' : ($tx->is_transfer ? '' : '-') }} {{ $tx->formatted_amount }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                <form method="POST" action="{{ route('transactions.destroy', $tx) }}" onsubmit="return confirm('Tem certeza que deseja apagar esta transacção?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs px-2 py-1 rounded hover:bg-rose-950/40 transition">
                                        🗑️
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400 text-xs">
                                Nenhuma transacção encontrada com os filtros actuais.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
