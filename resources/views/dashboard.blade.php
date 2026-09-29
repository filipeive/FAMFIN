@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Section with Month Switcher -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Painel Financeiro
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 font-normal">
                    {{ $family->name }}
                </span>
            </h1>
            <p class="text-xs text-slate-400 mt-0.5">Visão consolidada do fluxo de caixa e património</p>
        </div>

        <!-- Month Selector Form -->
        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2 bg-slate-900 p-1.5 rounded-xl border border-slate-800">
            <span class="text-xs text-slate-400 pl-2">📅 Mês:</span>
            <select name="month" onchange="this.form.submit()" class="bg-slate-950 text-white text-xs font-semibold rounded-lg px-2.5 py-1 border border-slate-700 focus:outline-none focus:border-emerald-500">
                @foreach($availableMonths as $m)
                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                        {{ $m }}
                    </option>
                @endforeach
            </select>
            <a href="{{ route('transactions.create') }}" class="px-3 py-1 rounded-lg text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition">
                + Nova Transacção
            </a>
        </form>
    </div>

    <!-- Monthly Salary & Financial Rules Breakdown Banner (The Signature Flow) -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-900/90 rounded-2xl border border-slate-800 p-5 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80 mb-4">
            <div class="flex items-center gap-2">
                <span class="text-lg">⚖️</span>
                <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wider">Regras & Fluxo do Mês ({{ $month }})</h2>
            </div>
            <a href="{{ route('rules.index') }}" class="text-xs text-emerald-400 hover:underline">Configurar percentuais &rarr;</a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <!-- 1. Gross Income -->
            <div class="bg-slate-950/70 rounded-xl p-3.5 border border-slate-800">
                <span class="text-[11px] font-semibold text-emerald-400 uppercase tracking-wide">1. Entrada Bruta</span>
                <p class="text-lg font-black text-white mt-1">{{ formatMoney($incomeBreakdown['gross_amount'], $currency) }}</p>
                <span class="text-[10px] text-slate-400">Salário / Receita</span>
            </div>

            <!-- 2. INSS -->
            <div class="bg-slate-950/70 rounded-xl p-3.5 border border-slate-800">
                <span class="text-[11px] font-semibold text-amber-400 uppercase tracking-wide">2. INSS (3%)</span>
                @php
                    $inssItem = collect($incomeBreakdown['deductions'])->firstWhere('type', 'inss');
                @endphp
                <p class="text-lg font-black text-amber-300 mt-1">- {{ formatMoney($inssItem['amount'] ?? 0, $currency) }}</p>
                <span class="text-[10px] text-slate-400">Segurança Social</span>
            </div>

            <!-- 3. Tithe (Dízimo) -->
            <div class="bg-slate-950/70 rounded-xl p-3.5 border border-slate-800">
                <span class="text-[11px] font-semibold text-sky-400 uppercase tracking-wide">3. Dízimo (10%)</span>
                @php
                    $titheItem = collect($incomeBreakdown['deductions'])->firstWhere('type', 'tithe');
                @endphp
                <p class="text-lg font-black text-sky-300 mt-1">- {{ formatMoney($titheItem['amount'] ?? 0, $currency) }}</p>
                <span class="text-[10px] text-slate-400">Contribuição</span>
            </div>

            <!-- 4. Net Income (Renda) -->
            <div class="bg-slate-950/70 rounded-xl p-3.5 border border-slate-800">
                <span class="text-[11px] font-semibold text-teal-400 uppercase tracking-wide">4. Renda Líquida</span>
                <p class="text-lg font-black text-teal-300 mt-1">{{ formatMoney($incomeBreakdown['net_amount'], $currency) }}</p>
                <span class="text-[10px] text-slate-400">Disponível</span>
            </div>

            <!-- 5. Month Expenses -->
            <div class="bg-slate-950/70 rounded-xl p-3.5 border border-slate-800">
                <span class="text-[11px] font-semibold text-rose-400 uppercase tracking-wide">5. Total de Gasto</span>
                <p class="text-lg font-black text-rose-400 mt-1">- {{ formatMoney($monthExpenses, $currency) }}</p>
                <span class="text-[10px] text-slate-400">Despesas reais</span>
            </div>

            <!-- 6. Month Final Balance -->
            <div class="bg-slate-950/70 rounded-xl p-3.5 border {{ $monthBalance >= 0 ? 'border-emerald-500/40 bg-emerald-950/20' : 'border-rose-500/40 bg-rose-950/20' }}">
                <span class="text-[11px] font-semibold {{ $monthBalance >= 0 ? 'text-emerald-400' : 'text-rose-400' }} uppercase tracking-wide">6. Saldo Final</span>
                <p class="text-lg font-black {{ $monthBalance >= 0 ? 'text-emerald-300' : 'text-rose-400' }} mt-1">
                    {{ formatMoney($monthBalance, $currency) }}
                </p>
                <span class="text-[10px] text-slate-400">{{ $monthBalance >= 0 ? 'Superávit' : 'Défice' }}</span>
            </div>
        </div>
    </div>

    <!-- KPI Grid: Net Worth, Accounts, Debts, Savings -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Net Worth Card -->
        <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs">
                <span>💎 Património Líquido</span>
                <span class="text-[10px] bg-slate-800 px-2 py-0.5 rounded text-slate-300">Total</span>
            </div>
            <p class="text-2xl font-black text-white mt-2">{{ $netWorth['formatted_net_worth'] }}</p>
            <div class="mt-2 text-[11px] text-slate-400 flex justify-between">
                <span>Activos: {{ $netWorth['formatted_assets_total'] }}</span>
                <span>Dívidas: {{ $netWorth['formatted_debts_total'] }}</span>
            </div>
        </div>

        <!-- Accounts Total Liquid Balance -->
        <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs">
                <span>🏦 Saldo nas Contas</span>
                <a href="{{ route('accounts.index') }}" class="text-[10px] text-emerald-400 hover:underline">Ver contas &rarr;</a>
            </div>
            <p class="text-2xl font-black text-emerald-400 mt-2">{{ $netWorth['formatted_accounts_total'] }}</p>
            <div class="mt-2 text-[11px] text-slate-400 flex justify-between">
                <span>{{ $family->accounts->count() }} conta(s) activas</span>
            </div>
        </div>

        <!-- Active Debts -->
        <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs">
                <span>💳 Dívidas Pendentes</span>
                <a href="{{ route('debts.index') }}" class="text-[10px] text-rose-400 hover:underline">Ver dívidas &rarr;</a>
            </div>
            <p class="text-2xl font-black text-rose-400 mt-2">{{ $debtSummary['formatted_total_remaining'] }}</p>
            <div class="mt-2 text-[11px] text-slate-400 flex justify-between">
                <span>{{ $debtSummary['active_debts_count'] }} activas</span>
                <span>Pago: {{ $debtSummary['progress_percentage'] }}%</span>
            </div>
        </div>

        <!-- Monthly Budget Consumed -->
        <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 text-xs">
                <span>🎯 Orçamento do Mês</span>
                <a href="{{ route('budgets.index', ['month' => $month]) }}" class="text-[10px] text-emerald-400 hover:underline">Detalhes &rarr;</a>
            </div>
            <p class="text-2xl font-black text-white mt-2">{{ $budgetOverview['overall_percentage'] }}%</p>
            <div class="mt-2 text-[11px] text-slate-400 flex justify-between">
                <span>Gasto: {{ $budgetOverview['formatted_total_spent'] }}</span>
                <span>Restante: {{ $budgetOverview['formatted_total_remaining'] }}</span>
            </div>
        </div>
    </div>

    <!-- Middle Section: Budgets Tracking & Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Budget Gauges / Category Progress (7 cols) -->
        <div class="lg:col-span-7 bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    🎯 Orçamentos por Categoria ({{ $month }})
                </h3>
                <a href="{{ route('budgets.index', ['month' => $month]) }}" class="text-xs text-emerald-400 hover:underline">
                    Gerir Orçamentos &rarr;
                </a>
            </div>

            @if(empty($budgetOverview['items']))
                <div class="text-center py-8 text-slate-400 text-xs">
                    Nenhum orçamento configurado para este mês.
                    <div class="mt-2">
                        <a href="{{ route('budgets.index', ['month' => $month]) }}" class="text-emerald-400 font-semibold underline">Configurar orçamentos agora</a>
                    </div>
                </div>
            @else
                <div class="space-y-3.5">
                    @foreach($budgetOverview['items'] as $item)
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-medium text-slate-200 flex items-center gap-1.5">
                                    <span>{{ $item['category_icon'] ?? '🏷️' }}</span>
                                    <span>{{ $item['category_name'] }}</span>
                                </span>
                                <span class="text-slate-400">
                                    <strong class="text-white">{{ $item['formatted_spent'] }}</strong> / {{ $item['formatted_budget'] }}
                                    <span class="font-semibold {{ $item['status'] === 'exceeded' ? 'text-rose-400' : ($item['status'] === 'warning' ? 'text-amber-400' : 'text-emerald-400') }}">
                                        ({{ $item['percentage_used'] }}%)
                                    </span>
                                </span>
                            </div>
                            <!-- Progress Bar -->
                            <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                                @php
                                    $width = min(100, $item['percentage_used']);
                                    $barColor = $item['status'] === 'exceeded' ? 'bg-rose-500' : ($item['status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500');
                                @endphp
                                <div class="h-full {{ $barColor }} rounded-full transition-all" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recent Activity / Transactions (5 cols) -->
        <div class="lg:col-span-5 bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    💸 Últimas Transacções
                </h3>
                <a href="{{ route('transactions.index', ['month' => $month]) }}" class="text-xs text-emerald-400 hover:underline">
                    Ver todas &rarr;
                </a>
            </div>

            @if($recentTransactions->isEmpty())
                <div class="text-center py-8 text-slate-400 text-xs">
                    Nenhuma transacção registada neste mês.
                </div>
            @else
                <div class="divide-y divide-slate-800/80">
                    @foreach($recentTransactions as $tx)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg {{ $tx->is_income ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($tx->is_transfer ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20') }} flex items-center justify-center font-bold text-xs">
                                    {{ $tx->is_income ? '↓' : ($tx->is_transfer ? '⇄' : '↑') }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-200">{{ $tx->description }}</p>
                                    <span class="text-[10px] text-slate-400">{{ $tx->transaction_date->format('d/m/Y') }} • {{ $tx->category?->name ?? 'Geral' }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-bold {{ $tx->is_income ? 'text-emerald-400' : ($tx->is_transfer ? 'text-sky-400' : 'text-slate-200') }}">
                                    {{ $tx->is_income ? '+' : ($tx->is_transfer ? '' : '-') }} {{ $tx->formatted_amount }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
