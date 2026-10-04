<x-layouts.app>
    <x-slot:header>
        Relatórios & Analytics Financeiros
    </x-slot:header>

    <!-- Header Actions & Month Selector -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 p-5 rounded-2xl border border-slate-800">
        <div class="flex items-center space-x-3">
            <x-icon name="reports" class="w-6 h-6 text-emerald-400" />
            <div>
                <p class="text-xs text-slate-400 font-medium">Análise Consolidada de Desempenho</p>
                <h3 class="text-base font-bold text-white">Fluxo de Caixa & Distribuição por Categorias</h3>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('reports.export', ['month' => $selectedMonth]) }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition">
                <x-icon name="download" class="w-4 h-4 mr-2" />
                Exportar CSV
            </a>
        </div>
    </div>

    <!-- Monthly Cash Flow Visual Bar Chart -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
            <div>
                <h3 class="text-base font-bold text-white">Evolução Mensal: Receitas vs Despesas</h3>
                <p class="text-xs text-slate-400">Comparativo dos últimos {{ $trend['months_count'] }} meses de movimentação</p>
            </div>
            <div class="flex items-center space-x-4 text-xs font-semibold">
                <span class="flex items-center text-teal-400">
                    <span class="w-3 h-3 rounded-full bg-teal-400 mr-1.5"></span> Receitas
                </span>
                <span class="flex items-center text-rose-400">
                    <span class="w-3 h-3 rounded-full bg-rose-400 mr-1.5"></span> Despesas
                </span>
            </div>
        </div>

        <!-- Visual Bar Chart -->
        <div class="h-64 flex items-end space-x-2 sm:space-x-4 pt-6 pb-2 border-b border-slate-800 overflow-x-auto">
            @foreach ($trend['items'] as $item)
                @php
                    $incHeight = $trend['max_value'] > 0 ? max(4, round(($item['income'] / $trend['max_value']) * 100)) : 0;
                    $expHeight = $trend['max_value'] > 0 ? max(4, round(($item['expense'] / $trend['max_value']) * 100)) : 0;
                @endphp
                <div class="flex-1 min-w-[40px] flex flex-col items-center h-full justify-end group relative">
                    <!-- Tooltip -->
                    <div class="absolute -top-12 hidden group-hover:flex flex-col items-center z-20 pointer-events-none">
                        <div class="bg-slate-800 text-[10px] text-slate-200 px-2.5 py-1 rounded-lg border border-slate-700 shadow-xl whitespace-nowrap">
                            <div>Rec: {{ $item['formatted_income'] }}</div>
                            <div>Desp: {{ $item['formatted_expense'] }}</div>
                            <div class="font-bold {{ $item['net'] >= 0 ? 'text-teal-400' : 'text-rose-400' }}">Saldo: {{ $item['formatted_net'] }}</div>
                        </div>
                        <div class="w-2 h-2 bg-slate-800 border-r border-b border-slate-700 rotate-45 -mt-1"></div>
                    </div>

                    <div class="w-full flex items-end justify-center space-x-1 h-full">
                        <div class="w-1/2 bg-teal-500/80 hover:bg-teal-400 rounded-t-md transition-all duration-300" style="height: {{ $incHeight }}%"></div>
                        <div class="w-1/2 bg-rose-500/80 hover:bg-rose-400 rounded-t-md transition-all duration-300" style="height: {{ $expHeight }}%"></div>
                    </div>

                    <span class="text-[10px] text-slate-400 mt-2 truncate w-full text-center">{{ $item['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Category Expense Breakdown & Net Worth -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Category Distribution (2 cols) -->
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h3 class="text-base font-bold text-white">Despesas por Categoria</h3>
                    <p class="text-xs text-slate-400">Total gasto no período selecionado: <strong class="text-slate-200">{{ $categoryBreakdown['formatted_total_expenses'] }}</strong></p>
                </div>

                <form method="GET" action="{{ route('reports.index') }}">
                    <select name="month" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todo o Histórico</option>
                        @foreach ($availableMonths as $m)
                            <option value="{{ $m }}" {{ $m === $selectedMonth ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- Categories Progress Bars -->
            <div class="space-y-4">
                @forelse ($categoryBreakdown['categories'] as $cat)
                    <div class="p-3.5 rounded-xl bg-slate-800/30 border border-slate-800 hover:border-slate-700 transition">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <div class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $cat['color'] }}"></span>
                                <span class="font-bold text-slate-200">{{ $cat['name'] }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="font-bold text-white">{{ $cat['formatted_amount'] }}</span>
                                <span class="font-bold text-slate-400 w-12 text-right">{{ $cat['percentage'] }}%</span>
                            </div>
                        </div>

                        <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500" style="width: {{ $cat['percentage'] }}%; background-color: {{ $cat['color'] }}"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-slate-500 text-xs">Nenhuma despesa registada para o período selecionado.</p>
                @endforelse
            </div>
        </div>

        <!-- Net Worth Summary (1 col) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-white mb-4">Composição Patrimonial</h3>

                <div class="space-y-4">
                    <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-700/50">
                        <span class="text-[10px] text-slate-500 uppercase tracking-wider block">Património Líquido Total</span>
                        <div class="text-2xl font-black text-emerald-400 mt-1">
                            {{ $netWorth['formatted_net_worth'] }}
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-800/20 border border-slate-800 text-xs space-y-2">
                        <div class="flex justify-between items-center text-slate-300">
                            <span>Saldos em Contas:</span>
                            <span class="font-bold text-white">{{ $netWorth['formatted_accounts_total'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-300">
                            <span>Bens & Imóveis:</span>
                            <span class="font-bold text-white">{{ $netWorth['formatted_assets_total'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-300">
                            <span>Poupanças Acumuladas:</span>
                            <span class="font-bold text-teal-400">{{ $netWorth['formatted_savings_total'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-rose-400 border-t border-slate-800 pt-2 font-semibold">
                            <span>Total em Dívidas:</span>
                            <span>-{{ $netWorth['formatted_debts_total'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-800 text-xs text-slate-400 flex items-center justify-between">
                <span>Taxa de Quitação de Dívidas:</span>
                <span class="font-bold text-teal-400">{{ $debtSummary['progress_percentage'] }}%</span>
            </div>
        </div>
    </div>
</x-layouts.app>
