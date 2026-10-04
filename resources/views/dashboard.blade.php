<x-layouts.app>
    <x-slot:header>
        Visão Geral Financeira
    </x-slot:header>

    <!-- Month Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 p-4 rounded-2xl border border-slate-800">
        <div class="flex items-center space-x-3">
            <x-icon name="calendar" class="w-5 h-5 text-emerald-400" />
            <div>
                <p class="text-xs text-slate-400 font-medium">Mês de Referência</p>
                <h3 class="text-base font-bold text-white">{{ \Carbon\Carbon::parse($currentMonth . '-01')->translatedFormat('F / Y') }}</h3>
            </div>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <select name="month" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                @foreach ($availableMonths as $m)
                    <option value="{{ $m }}" {{ $m === $currentMonth ? 'selected' : '' }}>
                        {{ $m }} ({{ \Carbon\Carbon::parse($m . '-01')->translatedFormat('M Y') }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Net Worth Card -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Património Líquido</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <x-icon name="scale" class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black tracking-tight text-white">{{ $netWorth['formatted_net_worth'] }}</div>
                <p class="text-xs text-slate-400 mt-1">Bens ({{ $netWorth['formatted_assets_total'] }}) &bull; Dívidas ({{ $netWorth['formatted_debts_total'] }})</p>
            </div>
        </div>

        <!-- Monthly Income Card -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Receitas do Mês</span>
                <div class="w-9 h-9 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400">
                    <x-icon name="income" class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black tracking-tight text-teal-400">{{ $formattedMonthlyIncome }}</div>
                <p class="text-xs text-slate-400 mt-1">Entradas em {{ $currentMonth }}</p>
            </div>
        </div>

        <!-- Monthly Expenses Card -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Despesas do Mês</span>
                <div class="w-9 h-9 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400">
                    <x-icon name="expense" class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black tracking-tight text-rose-400">{{ $formattedMonthlyExpense }}</div>
                <p class="text-xs text-slate-400 mt-1">Gastos em {{ $currentMonth }}</p>
            </div>
        </div>

        <!-- Monthly Cash Balance / Savings Card -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Saldo Operacional</span>
                <div class="w-9 h-9 rounded-xl {{ $monthlyBalance >= 0 ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-rose-500/10 border border-rose-500/20 text-rose-400' }} flex items-center justify-center">
                    <x-icon name="wallet" class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black tracking-tight {{ $monthlyBalance >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">{{ $formattedMonthlyBalance }}</div>
                <p class="text-xs text-slate-400 mt-1">{{ $monthlyBalance >= 0 ? 'Superávit líquido no mês' : 'Déficit líquido no mês' }}</p>
            </div>
        </div>
    </div>

    <!-- Middle Section: Recent Transactions & Budget Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Transactions (2 cols) -->
        <div class="lg:col-span-2 bg-slate-900 rounded-2xl border border-slate-800 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <x-icon name="transactions" class="w-5 h-5 text-emerald-400" />
                    <h3 class="text-base font-bold text-white">Transações Recentes</h3>
                </div>
                <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300">
                    Ver todas &rarr;
                </a>
            </div>

            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                            <th class="pb-3">Data</th>
                            <th class="pb-3">Descrição</th>
                            <th class="pb-3">Conta / Categoria</th>
                            <th class="pb-3 text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse ($recentTransactions as $tx)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3 text-xs text-slate-400">{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}</td>
                                <td class="py-3">
                                    <div class="font-semibold text-slate-200">{{ $tx->description }}</div>
                                </td>
                                <td class="py-3 text-xs text-slate-400">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-800 border border-slate-700/50 text-slate-300">
                                        {{ $tx->account->name ?? '—' }}
                                    </span>
                                    @if ($tx->category)
                                        <span class="ml-1 text-slate-500">&bull; {{ $tx->category->name }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right font-bold text-xs {{ $tx->type->value === 'income' ? 'text-teal-400' : ($tx->type->value === 'expense' ? 'text-rose-400' : 'text-slate-300') }}">
                                    {{ $tx->type->value === 'income' ? '+' : ($tx->type->value === 'expense' ? '-' : '') }}{{ formatMoney($tx->amount, $family->currency) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500 text-xs">
                                    Nenhuma transação registada até ao momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Budget Overview (1 col) -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <x-icon name="budget" class="w-5 h-5 text-emerald-400" />
                    <h3 class="text-base font-bold text-white">Orçamento do Mês</h3>
                </div>
                <a href="{{ route('budgets.index', ['month' => $currentMonth]) }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300">
                    Gerir &rarr;
                </a>
            </div>

            <div class="mb-4 p-4 rounded-xl bg-slate-800/40 border border-slate-700/50">
                <div class="flex justify-between text-xs text-slate-400 mb-1">
                    <span>Gasto vs Orçado</span>
                    <span class="font-bold text-slate-200">{{ $budgetOverview['overall_percentage'] }}%</span>
                </div>
                <div class="w-full h-2.5 bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full {{ $budgetOverview['overall_percentage'] > 100 ? 'bg-rose-500' : ($budgetOverview['overall_percentage'] > 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $budgetOverview['overall_percentage']) }}%"></div>
                </div>
                <div class="flex justify-between text-xs mt-2">
                    <span class="text-slate-400">{{ $budgetOverview['formatted_total_spent'] }}</span>
                    <span class="font-bold text-slate-300">{{ $budgetOverview['formatted_total_budgeted'] }}</span>
                </div>
            </div>

            <div class="space-y-3 flex-1 overflow-y-auto max-h-64">
                @forelse (array_slice($budgetOverview['items'], 0, 5) as $item)
                    <div class="p-2.5 rounded-xl bg-slate-800/30 border border-slate-800 text-xs">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-slate-200 truncate">{{ $item['category_name'] }}</span>
                            <span class="font-bold {{ $item['status'] === 'exceeded' ? 'text-rose-400' : ($item['status'] === 'warning' ? 'text-amber-400' : 'text-emerald-400') }}">
                                {{ $item['percentage_used'] }}%
                            </span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full {{ $item['status'] === 'exceeded' ? 'bg-rose-500' : ($item['status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $item['percentage_used']) }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                            <span>Gasto: {{ $item['formatted_spent'] }}</span>
                            <span>Restante: {{ $item['formatted_remaining'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-500 text-xs text-center py-6">Nenhum orçamento configurado para este mês.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Bottom Section: Savings Goals & Debt Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Savings Goals -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <x-icon name="savings" class="w-5 h-5 text-emerald-400" />
                    <h3 class="text-base font-bold text-white">Metas de Poupança Ativas</h3>
                </div>
                <a href="{{ route('savings.index') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300">
                    Ver todas &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse ($savingsGoals as $goal)
                    <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-700/50 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start">
                                <h4 class="font-bold text-sm text-slate-200">{{ $goal['name'] }}</h4>
                                <span class="text-xs font-bold text-emerald-400">{{ $goal['progress_percentage'] }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden my-3">
                                <div class="h-full bg-emerald-500" style="width: {{ min(100, $goal['progress_percentage']) }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-slate-400">
                                <span>{{ $goal['formatted_current'] }}</span>
                                <span class="text-slate-500">de {{ $goal['formatted_target'] }}</span>
                            </div>
                        </div>
                        @if ($goal['formatted_monthly_needed'])
                            <div class="mt-3 pt-2 border-t border-slate-700/40 text-[11px] text-amber-300/90 font-medium">
                                Recomendado: {{ $goal['formatted_monthly_needed'] }}/mês
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-2 py-6 text-center text-slate-500 text-xs">
                        Nenhuma meta de poupança ativa.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Debts & Loans -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <x-icon name="debt" class="w-5 h-5 text-rose-400" />
                    <h3 class="text-base font-bold text-white">Amortização de Dívidas</h3>
                </div>
                <a href="{{ route('debts.index') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300">
                    Detalhes &rarr;
                </a>
            </div>

            <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-700/50 mb-4">
                <div class="flex justify-between text-xs text-slate-400 mb-1">
                    <span>Progresso de Pagamento</span>
                    <span class="font-bold text-teal-400">{{ $debtSummary['progress_percentage'] }}% quitado</span>
                </div>
                <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-teal-500" style="width: {{ min(100, $debtSummary['progress_percentage']) }}%"></div>
                </div>
                <div class="flex justify-between text-xs mt-3">
                    <div>
                        <span class="text-[10px] text-slate-500 uppercase block">Saldo Devedor</span>
                        <span class="font-bold text-rose-400 text-sm">{{ $debtSummary['formatted_total_remaining'] }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-500 uppercase block">Total Original</span>
                        <span class="font-bold text-slate-300 text-sm">{{ $debtSummary['formatted_total_original'] }}</span>
                    </div>
                </div>
            </div>

            <div class="text-xs text-slate-400 flex items-center justify-between px-2">
                <span>Dívidas ativas em monitoramento:</span>
                <span class="font-bold text-slate-200">{{ $debtSummary['active_debts_count'] }}</span>
            </div>
        </div>
    </div>
</x-layouts.app>
