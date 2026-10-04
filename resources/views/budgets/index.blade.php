<x-layouts.app>
    <x-slot:header>
        Gestão de Orçamentos Mensais
    </x-slot:header>

    <!-- Month Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 p-5 rounded-2xl border border-slate-800">
        <div class="flex items-center space-x-3">
            <x-icon name="budget" class="w-6 h-6 text-emerald-400" />
            <div>
                <p class="text-xs text-slate-400 font-medium">Orçamento para</p>
                <h3 class="text-lg font-bold text-white">{{ \Carbon\Carbon::parse($currentMonth . '-01')->translatedFormat('F / Y') }}</h3>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <form method="GET" action="{{ route('budgets.index') }}">
                <input type="month" name="month" value="{{ $currentMonth }}" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
            </form>

            <button onclick="document.getElementById('copyModal').classList.toggle('hidden')" class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition">
                <x-icon name="copy" class="w-4 h-4 mr-1.5" />
                Copiar de Outro Mês
            </button>
        </div>
    </div>

    <!-- Summary KPI Card -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Progresso Geral do Mês</span>
                <div class="text-2xl font-black text-white mt-1">
                    {{ $overview['formatted_total_spent'] }} <span class="text-sm font-normal text-slate-400">/ {{ $overview['formatted_total_budgeted'] }}</span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Restante Global</span>
                <div class="text-xl font-bold {{ $overview['total_remaining'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }} mt-1">
                    {{ $overview['formatted_total_remaining'] }}
                </div>
            </div>
        </div>

        <div class="w-full h-3 bg-slate-800 rounded-full overflow-hidden">
            <div class="h-full {{ $overview['overall_percentage'] > 100 ? 'bg-rose-500' : ($overview['overall_percentage'] > 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $overview['overall_percentage']) }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-slate-400 mt-2">
            <span>{{ $overview['overall_percentage'] }}% consumido</span>
            <span>Limite de Alerta Padrão: 80%</span>
        </div>
    </div>

    <!-- Budget List & Set Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Budget Items List (2 cols) -->
        <div class="lg:col-span-2 bg-slate-900 rounded-2xl border border-slate-800 p-6">
            <h3 class="text-base font-bold text-white mb-4">Orçamento por Categoria</h3>

            <div class="space-y-4">
                @forelse ($overview['items'] as $item)
                    <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-700/50 hover:border-slate-600 transition">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <x-icon name="tag" class="w-4 h-4 text-slate-400" />
                                <span class="font-bold text-sm text-slate-100">{{ $item['category_name'] }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if ($item['status'] === 'exceeded')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        Excedido
                                    </span>
                                @elseif ($item['status'] === 'warning')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Atenção
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Normal
                                    </span>
                                @endif
                                <span class="text-xs font-bold text-slate-200">{{ $item['percentage_used'] }}%</span>

                                @if (!empty($item['budget_id']))
                                    <form method="POST" action="{{ route('budgets.destroy', $item['budget_id']) }}" id="delete-budget-{{ $item['budget_id'] }}" class="inline-block ml-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" 
                                                onclick="confirmAction('delete-budget-{{ $item['budget_id'] }}', 'Eliminar Orçamento?', 'Deseja remover o limite de orçamento para \'{{ $item['category_name'] }}\'?', 'Sim, Eliminar')"
                                                title="Eliminar Limite" 
                                                class="p-1 rounded text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition">
                                            <x-icon name="trash" class="w-3.5 h-3.5" />
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden my-2">
                            <div class="h-full {{ $item['status'] === 'exceeded' ? 'bg-rose-500' : ($item['status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $item['percentage_used']) }}%"></div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-slate-400">
                            <div>
                                <span>Gasto: <strong class="text-slate-200">{{ $item['formatted_spent'] }}</strong></span>
                                <span class="text-slate-600 mx-1">&bull;</span>
                                <span>Teto: <strong class="text-slate-200">{{ $item['formatted_budget'] }}</strong></span>
                            </div>
                            <div>
                                <span>Disponível: <strong class="{{ $item['remaining_amount'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">{{ $item['formatted_remaining'] }}</strong></span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-500 text-xs">
                        Nenhum orçamento configurado para o mês de {{ $currentMonth }}. Utilize o formulário ao lado para definir limites.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Set / Update Budget Form (1 col) -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 flex flex-col">
            <h3 class="text-base font-bold text-white mb-4">Definir / Atualizar Limite</h3>

            <form method="POST" action="{{ route('budgets.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="month" value="{{ $currentMonth }}">

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Categoria</label>
                    <select name="category_id" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Selecione a categoria...</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Limite Orçado (MZN)</label>
                    <input type="text" name="amount" required placeholder="ex: 5.000,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-sm rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Alerta de Proximidade (%)</label>
                    <input type="number" name="alert_threshold" value="80" min="1" max="100" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    <p class="text-[10px] text-slate-500 mt-1">Notificar quando os gastos atingirem esta percentagem.</p>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-emerald-500/20">
                    Guardar Orçamento
                </button>
            </form>
        </div>
    </div>

    <!-- Modal for Copying Budgets -->
    <div id="copyModal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-white mb-2">Copiar Orçamentos de Outro Mês</h3>
            <p class="text-xs text-slate-400 mb-4">Replica todos os limites estabelecidos de um mês base para um mês alvo.</p>

            <form method="POST" action="{{ route('budgets.copy') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Mês de Origem</label>
                    <input type="month" name="from_month" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Mês de Destino</label>
                    <input type="month" name="to_month" required value="{{ $currentMonth }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3">
                    <button type="button" onclick="document.getElementById('copyModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-500 text-slate-950 hover:bg-emerald-400 transition">
                        Confirmar Cópia
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
