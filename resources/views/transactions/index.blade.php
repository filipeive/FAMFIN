<x-layouts.app>
    <x-slot:header>
        Transações Financeiras
    </x-slot:header>

    <div x-data="{
        view: window.innerWidth < 768 ? 'grid' : (localStorage.getItem('famfin_transactions_view') || 'grid'),
        updateView() {
            if (window.innerWidth < 768 && this.view === 'list') {
                this.view = 'grid';
            }
        }
    }"
    x-init="$watch('view', val => localStorage.setItem('famfin_transactions_view', val))"
    @resize.window.debounce.300ms="updateView()"
    class="space-y-5">

        <!-- Top Header & View Mode Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-800">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-400">Livro de Movimentações</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                        {{ $transactions->total() }} registos
                    </span>
                </div>
                <h2 class="text-lg sm:text-xl font-black text-white mt-0.5">Histórico de Transações</h2>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- Grid / List Switcher (Available on all screens, mobile defaults to grid) -->
                <div class="flex bg-slate-800/90 p-1 rounded-xl items-center border border-slate-700/60 shadow-sm">
                    <button type="button" @click="view = 'grid'"
                        :class="view === 'grid' ? 'bg-emerald-500 text-slate-950 shadow-sm font-black' : 'text-slate-400 hover:text-white font-semibold'"
                        class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
                        <x-icon name="grid" class="w-3.5 h-3.5" />
                        <span>Grelha</span>
                    </button>
                    <button type="button" @click="view = 'list'"
                        :class="view === 'list' ? 'bg-emerald-500 text-slate-950 shadow-sm font-black' : 'text-slate-400 hover:text-white font-semibold'"
                        class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
                        <x-icon name="list" class="w-3.5 h-3.5" />
                        <span>Lista</span>
                    </button>
                </div>

                <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition active:scale-95">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Nova</span>
                </a>
            </div>
        </div>

        <!-- Filters Bar -->
        <div class="bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-800 space-y-4">
            <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <!-- Year Filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Ano</label>
                    <select name="year" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-2.5 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todos os anos</option>
                        @foreach ($availableYears as $y)
                            <option value="{{ $y }}" {{ ($filters['year'] ?? '') === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Month Filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Mês</label>
                    <select name="month" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-2.5 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todos os meses</option>
                        @foreach ($availableMonths as $m)
                            <option value="{{ $m }}" {{ ($filters['month'] ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tipo</label>
                    <select name="type" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-2.5 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todos os tipos</option>
                        <option value="income" {{ ($filters['type'] ?? '') === 'income' ? 'selected' : '' }}>Receitas</option>
                        <option value="expense" {{ ($filters['type'] ?? '') === 'expense' ? 'selected' : '' }}>Despesas</option>
                        <option value="transfer" {{ ($filters['type'] ?? '') === 'transfer' ? 'selected' : '' }}>Transferências</option>
                    </select>
                </div>

                <!-- Account Filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Conta</label>
                    <select name="account_id" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-2.5 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todas as contas</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ ($filters['account_id'] ?? '') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Categoria</label>
                    <select name="category_id" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-2.5 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Todas as categorias</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search & Submit -->
                <div class="col-span-2 sm:col-span-2 lg:col-span-1 flex items-end space-x-2">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pesquisar</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Descrição..." class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-500 text-slate-950 font-bold hover:bg-emerald-400 transition" title="Filtrar">
                        <x-icon name="filter" class="w-4 h-4" />
                    </button>
                </div>
            </form>

            <!-- Bulk Selection Action Bar (Hidden until rows are selected) -->
            <div id="bulk-action-bar" class="hidden flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 rounded-xl bg-slate-800/90 border border-rose-500/30 transition-all">
                <div class="flex items-center space-x-3 text-xs text-slate-200">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    <span><strong id="selected-count" class="text-rose-400 font-bold">0</strong> transações selecionadas</span>
                </div>

                <form id="bulk-delete-form" method="POST" action="{{ route('transactions.bulk_delete') }}">
                    @csrf
                    <div id="bulk-hidden-inputs"></div>
                    <button type="button" onclick="executeBulkDelete()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-bold bg-rose-500 hover:bg-rose-400 text-white transition shadow-lg shadow-rose-500/20">
                        <x-icon name="trash" class="w-4 h-4 mr-1.5" />
                        Eliminar Selecionadas
                    </button>
                </form>
            </div>
        </div>

        <!-- 1. VISTA GRELHA (GRID VIEW - Padrão em Mobile e Cartões Touch) -->
        <div x-show="view === 'grid'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
            @forelse ($transactions as $tx)
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700/80 transition-all flex flex-col justify-between shadow-sm relative group">
                    <div>
                        <!-- Header do Card: Checkbox, Ícone do Tipo e Data -->
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <input type="checkbox" value="{{ $tx->id }}" class="tx-checkbox rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-slate-900 w-4 h-4 cursor-pointer">
                                
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 {{ $tx->type->value === 'income' ? 'bg-teal-500/10 border border-teal-500/20 text-teal-400' : ($tx->type->value === 'expense' ? 'bg-rose-500/10 border border-rose-500/20 text-rose-400' : 'bg-indigo-500/10 border border-indigo-500/20 text-indigo-400') }}">
                                    @if ($tx->type->value === 'income')
                                        <x-icon name="income" class="w-4 h-4" />
                                    @elseif ($tx->type->value === 'expense')
                                        <x-icon name="expense" class="w-4 h-4" />
                                    @else
                                        <x-icon name="transfer" class="w-4 h-4" />
                                    @endif
                                </div>

                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $tx->type->value === 'income' ? 'bg-teal-500/10 text-teal-400 border border-teal-500/20' : ($tx->type->value === 'expense' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20') }}">
                                    {{ $tx->type->value === 'income' ? 'Receita' : ($tx->type->value === 'expense' ? 'Despesa' : 'Transf.') }}
                                </span>
                            </div>

                            <span class="text-[11px] font-mono text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded-md flex-shrink-0">
                                {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}
                            </span>
                        </div>

                        <!-- Valor & Descrição -->
                        <div class="mb-3">
                            <div class="text-xl sm:text-2xl font-black tracking-tight {{ $tx->type->value === 'income' ? 'text-teal-400' : ($tx->type->value === 'expense' ? 'text-rose-400' : 'text-slate-100') }}">
                                {{ $tx->type->value === 'income' ? '+' : ($tx->type->value === 'expense' ? '-' : '') }}{{ formatMoney($tx->amount, $family->currency ?? 'MZN') }}
                            </div>
                            <h4 class="font-bold text-sm text-slate-200 mt-1 line-clamp-2 leading-snug">{{ $tx->description }}</h4>
                            @if ($tx->notes)
                                <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1 italic">{{ $tx->notes }}</p>
                            @endif
                        </div>

                        <!-- Metadados em Bloco Compacto -->
                        <div class="space-y-1.5 p-2.5 rounded-xl bg-slate-950/40 border border-slate-800/60 text-[11px] mb-3">
                            <div class="flex items-center justify-between text-slate-400">
                                <span class="text-slate-500 text-[10px] uppercase font-bold">Conta</span>
                                <span class="font-medium text-slate-300 truncate max-w-[140px]">{{ $tx->account->name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-400">
                                <span class="text-slate-500 text-[10px] uppercase font-bold">Categoria</span>
                                <span class="font-medium text-slate-300 truncate max-w-[140px]">{{ $tx->category->name ?? 'Sem categoria' }}</span>
                            </div>
                            @if ($tx->transferAccount)
                                <div class="flex items-center justify-between text-slate-400">
                                    <span class="text-slate-500 text-[10px] uppercase font-bold">Destino</span>
                                    <span class="font-medium text-indigo-300 truncate max-w-[140px]">&rarr; {{ $tx->transferAccount->name }}</span>
                                </div>
                            @endif
                            @if ($tx->user)
                                <div class="flex items-center justify-between text-slate-400">
                                    <span class="text-slate-500 text-[10px] uppercase font-bold">Membro</span>
                                    <span class="font-medium text-slate-300">{{ $tx->user->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Rodapé do Card: Ações Rápidas -->
                    <div class="pt-2 border-t border-slate-800/80 flex items-center justify-end gap-2">
                        <a href="{{ route('transactions.edit', $tx) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-emerald-400 transition" title="Editar">
                            <x-icon name="pencil" class="w-3.5 h-3.5" />
                            <span>Editar</span>
                        </a>
                        <form method="POST" action="{{ route('transactions.destroy', $tx) }}" id="delete-tx-grid-{{ $tx->id }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="confirmAction('delete-tx-grid-{{ $tx->id }}', 'Eliminar Transação', 'Deseja realmente eliminar esta transação? O saldo da conta será recalculado automaticamente.')" title="Eliminar" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 transition">
                                <x-icon name="trash" class="w-3.5 h-3.5" />
                                <span>Eliminar</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-12 text-center text-slate-500 text-xs bg-slate-900 rounded-2xl border border-slate-800">
                    <x-icon name="transactions" class="w-8 h-8 mx-auto text-slate-600 mb-2" />
                    <p class="font-bold text-slate-300">Nenhuma transação encontrada</p>
                    <p class="text-slate-500 mt-0.5">Tente ajustar os filtros ou crie uma nova transação.</p>
                </div>
            @endforelse
        </div>

        <!-- 2. VISTA LISTA (TABLE VIEW - Alternativa para Desktop) -->
        <div x-show="view === 'list'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-800/60 border-b border-slate-800 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                        <tr>
                            <th class="w-10 px-4 py-3.5 text-center">
                                <input type="checkbox" id="select-all" class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-slate-900 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="px-4 py-3.5">Data</th>
                            <th class="px-4 py-3.5">Descrição</th>
                            <th class="px-4 py-3.5">Tipo</th>
                            <th class="px-4 py-3.5">Categoria</th>
                            <th class="px-4 py-3.5">Conta</th>
                            <th class="px-4 py-3.5 text-right">Valor</th>
                            <th class="px-4 py-3.5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse ($transactions as $tx)
                            <tr class="hover:bg-slate-800/30 transition group" data-tx-row>
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" value="{{ $tx->id }}" class="tx-checkbox rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-slate-900 w-4 h-4 cursor-pointer">
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-200 text-xs">{{ $tx->description }}</div>
                                    @if ($tx->notes)
                                        <div class="text-[10px] text-slate-500 mt-0.5">{{ $tx->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($tx->type->value === 'income')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-teal-500/10 text-teal-400 border border-teal-500/20">
                                            Receita
                                        </span>
                                    @elseif ($tx->type->value === 'expense')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            Despesa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                            Transferência
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-300 whitespace-nowrap">
                                    {{ $tx->category->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap">
                                    <span>{{ $tx->account->name ?? '—' }}</span>
                                    @if ($tx->transferAccount)
                                        <span class="text-slate-500">&rarr; {{ $tx->transferAccount->name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-xs whitespace-nowrap {{ $tx->type->value === 'income' ? 'text-teal-400' : ($tx->type->value === 'expense' ? 'text-rose-400' : 'text-slate-200') }}">
                                    {{ $tx->type->value === 'income' ? '+' : ($tx->type->value === 'expense' ? '-' : '') }}{{ formatMoney($tx->amount, $family->currency ?? 'MZN') }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-1">
                                        <a href="{{ route('transactions.edit', $tx) }}" title="Editar" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-400 hover:bg-emerald-500/10 transition">
                                            <x-icon name="pencil" class="w-3.5 h-3.5" />
                                        </a>
                                        <form method="POST" action="{{ route('transactions.destroy', $tx) }}" id="delete-tx-list-{{ $tx->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmAction('delete-tx-list-{{ $tx->id }}', 'Eliminar Transação', 'Deseja realmente eliminar esta transação? O saldo da conta será recalculado automaticamente.')" title="Eliminar" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition">
                                                <x-icon name="trash" class="w-3.5 h-3.5" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-500 text-xs">
                                    Nenhuma transação encontrada com os filtros selecionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginação Compartilhada -->
        @if ($transactions->hasPages())
            <div class="p-4 rounded-2xl border border-slate-800 bg-slate-900/60">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Bulk Selection JavaScript -->
    <script>
        function initBulkSelection() {
            const selectAll = document.getElementById('select-all');
            const bulkActionBar = document.getElementById('bulk-action-bar');
            const selectedCount = document.getElementById('selected-count');
            const bulkHiddenInputs = document.getElementById('bulk-hidden-inputs');

            function getSelectedIds() {
                const checkedBoxes = document.querySelectorAll('.tx-checkbox:checked');
                const ids = new Set();
                checkedBoxes.forEach(cb => ids.add(cb.value));
                return Array.from(ids);
            }

            function updateBulkBar() {
                const selectedIds = getSelectedIds();
                if (selectedCount) {
                    selectedCount.textContent = selectedIds.length;
                }

                if (selectedIds.length > 0) {
                    bulkActionBar.classList.remove('hidden');
                    bulkHiddenInputs.innerHTML = '';
                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'transaction_ids[]';
                        input.value = id;
                        bulkHiddenInputs.appendChild(input);
                    });
                } else {
                    bulkActionBar.classList.add('hidden');
                    bulkHiddenInputs.innerHTML = '';
                }
            }

            document.querySelectorAll('.tx-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    // Sync same ID checkbox if present in other view
                    const sameCheckboxes = document.querySelectorAll(`.tx-checkbox[value="${this.value}"]`);
                    sameCheckboxes.forEach(other => other.checked = this.checked);
                    updateBulkBar();
                });
            });

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    const allCbs = document.querySelectorAll('.tx-checkbox');
                    allCbs.forEach(cb => cb.checked = selectAll.checked);
                    updateBulkBar();
                });
            }

            window.executeBulkDelete = function() {
                const count = getSelectedIds().length;
                if (count === 0) return;

                confirmAction(
                    'bulk-delete-form',
                    `Eliminar ${count} Transações`,
                    `Tem a certeza que deseja eliminar permanentemente ${count} transações? Os saldos de todas as contas associadas serão recalculados atomicamente.`,
                    'Sim, Eliminar Todas'
                );
            };
        }

        document.addEventListener('DOMContentLoaded', initBulkSelection);
    </script>
</x-layouts.app>
