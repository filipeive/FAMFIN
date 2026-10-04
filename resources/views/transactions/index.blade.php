<x-layouts.app>
    <x-slot:header>
        Transações Financeiras
    </x-slot:header>

    <!-- Filters Bar -->
    <div class="bg-slate-900 p-5 rounded-2xl border border-slate-800 space-y-4">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <!-- Year Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Ano</label>
                <select name="year" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos os anos</option>
                    @foreach ($availableYears as $y)
                        <option value="{{ $y }}" {{ ($filters['year'] ?? '') === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Month Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Mês</label>
                <select name="month" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos os meses</option>
                    @foreach ($availableMonths as $m)
                        <option value="{{ $m }}" {{ ($filters['month'] ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Type Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tipo</label>
                <select name="type" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todos os tipos</option>
                    <option value="income" {{ ($filters['type'] ?? '') === 'income' ? 'selected' : '' }}>Receitas</option>
                    <option value="expense" {{ ($filters['type'] ?? '') === 'expense' ? 'selected' : '' }}>Despesas</option>
                    <option value="transfer" {{ ($filters['type'] ?? '') === 'transfer' ? 'selected' : '' }}>Transferências</option>
                </select>
            </div>

            <!-- Account Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Conta</label>
                <select name="account_id" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todas as contas</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ ($filters['account_id'] ?? '') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Categoria</label>
                <select name="category_id" class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Todas as categorias</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search & Submit -->
            <div class="flex items-end space-x-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pesquisar</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Descrição..." class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-500 text-slate-950 font-bold hover:bg-emerald-400 transition">
                    <x-icon name="filter" class="w-4 h-4" />
                </button>
            </div>
        </form>

        <!-- Bulk Selection Action Bar (Hidden until rows are selected) -->
        <div id="bulk-action-bar" class="hidden flex items-center justify-between p-3 rounded-xl bg-slate-800/90 border border-rose-500/30 transition-all">
            <div class="flex items-center space-x-3 text-xs text-slate-200">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span><strong id="selected-count" class="text-rose-400 font-bold">0</strong> transações selecionadas</span>
            </div>

            <form id="bulk-delete-form" method="POST" action="{{ route('transactions.bulk_delete') }}">
                @csrf
                <div id="bulk-hidden-inputs"></div>
                <button type="button" onclick="executeBulkDelete()" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-rose-500 hover:bg-rose-400 text-white transition shadow-lg shadow-rose-500/20">
                    <x-icon name="trash" class="w-4 h-4 mr-1.5" />
                    Eliminar Selecionadas
                </button>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden shadow-sm">
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
                                    <form method="POST" action="{{ route('transactions.destroy', $tx) }}" id="delete-tx-{{ $tx->id }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmAction('delete-tx-{{ $tx->id }}', 'Eliminar Transação', 'Deseja realmente eliminar esta transação? O saldo da conta será recalculado automaticamente.')" title="Eliminar" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition">
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

        @if ($transactions->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-900/60">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Bulk Selection JavaScript -->
    <script>
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.tx-checkbox');
        const bulkActionBar = document.getElementById('bulk-action-bar');
        const selectedCount = document.getElementById('selected-count');
        const bulkHiddenInputs = document.getElementById('bulk-hidden-inputs');

        function updateBulkBar() {
            const selected = Array.from(checkboxes).filter(cb => cb.checked);
            selectedCount.textContent = selected.length;

            if (selected.length > 0) {
                bulkActionBar.classList.remove('hidden');
                // Populate hidden inputs
                bulkHiddenInputs.innerHTML = '';
                selected.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'transaction_ids[]';
                    input.value = cb.value;
                    bulkHiddenInputs.appendChild(input);
                });
            } else {
                bulkActionBar.classList.add('hidden');
                bulkHiddenInputs.innerHTML = '';
            }

            if (selectAll) {
                selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateBulkBar();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkBar);
        });

        function executeBulkDelete() {
            const count = Array.from(checkboxes).filter(cb => cb.checked).length;
            if (count === 0) return;

            confirmAction(
                'bulk-delete-form',
                `Eliminar ${count} Transações`,
                `Tem a certeza que deseja eliminar permanentemente ${count} transações? Os saldos de todas as contas associadas serão recalculados atomicamente.`,
                'Sim, Eliminar Todas'
            );
        }
    </script>
</x-layouts.app>
