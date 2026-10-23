<x-layouts.app>
    <x-slot:header>
        Contas & Carteiras
    </x-slot:header>

    <div class="space-y-6">
        <!-- Header Actions & Total Balance -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 p-4 sm:p-6 rounded-2xl border border-slate-800">
            <div>
                <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Saldo Consolidado da Família</span>
                <div class="text-2xl sm:text-3xl font-black text-emerald-400 mt-1">
                    {{ $formattedTotalBalance }}
                </div>
                <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5">Soma das contas ativas que integram o caixa geral</p>
            </div>

            <div class="flex items-center space-x-3">
                <form method="POST" action="{{ route('accounts.recalculate') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition">
                        <x-icon name="refresh" class="w-4 h-4 mr-2" />
                        Recalcular Saldos
                    </button>
                </form>
            </div>
        </div>

        <!-- 👧 SUBCONTAS & MESADAS DAS FILHAS (Ayume & Eunice) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-pink-500/10 border border-pink-500/20 flex items-center justify-center text-pink-400 flex-shrink-0 text-xl">
                        👧
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white">Subcontas de Mesadas — Filhas</h3>
                        <p class="text-xs text-slate-400">Envelopes mensais de 5.000,00 MT para gastos dedicados de cada filha</p>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-pink-500/10 text-pink-300 border border-pink-500/20 w-fit">
                    Envelope Mensal Ativo
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse ($allowanceAccounts as $allowance)
                    @php
                        $target = $allowance->allowance_monthly_amount ?? 500000;
                        $spentThisMonth = (int) $allowance->transactions()
                            ->where('type', \App\Enums\TransactionType::Expense)
                            ->where('status', \App\Enums\TransactionStatus::Completed)
                            ->whereMonth('transaction_date', now()->month)
                            ->whereYear('transaction_date', now()->year)
                            ->sum('amount');
                        $percentSpent = $target > 0 ? min(100, round(($spentThisMonth / $target) * 100)) : 0;
                    @endphp
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-800/40 border border-slate-700/60 hover:border-slate-600 transition flex flex-col justify-between shadow-sm relative overflow-hidden group">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-lg" style="background-color: {{ $allowance->color }}20; color: {{ $allowance->color }}; border: 1px solid {{ $allowance->color }}40;">
                                        {{ $allowance->icon ?? '👧' }}
                                    </div>
                                    <div>
                                        <h4 class="font-black text-base text-white">{{ $allowance->name }}</h4>
                                        <p class="text-[11px] text-slate-400">Teto mensal: {{ formatMoney($target, $family->currency) }}</p>
                                    </div>
                                </div>

                                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-900/80 text-slate-300 border border-slate-700">
                                    {{ $allowance->transactions_count }} mov.
                                </span>
                            </div>

                            <div class="my-3 p-3 rounded-xl bg-slate-900/70 border border-slate-800">
                                <div class="flex justify-between items-baseline mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Saldo Disponível</span>
                                    <span class="text-xl sm:text-2xl font-black {{ $allowance->balance > 0 ? 'text-emerald-400' : ($allowance->balance == 0 ? 'text-slate-300' : 'text-rose-400') }}">
                                        {{ formatMoney($allowance->balance, $family->currency) }}
                                    </span>
                                </div>

                                <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden my-2">
                                    <div class="h-full {{ $percentSpent > 85 ? 'bg-rose-500' : ($percentSpent > 50 ? 'bg-amber-500' : 'bg-emerald-500') }} transition-all duration-500" style="width: {{ $percentSpent }}%"></div>
                                </div>

                                <div class="flex justify-between text-[10px] text-slate-400">
                                    <span>Gasto no mês: <strong class="text-slate-200">{{ formatMoney($spentThisMonth, $family->currency) }}</strong></span>
                                    <span>{{ $percentSpent }}% do teto</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-700/50 flex flex-col sm:flex-row items-center gap-2">
                            <!-- Botão de Atribuir Mesada -->
                            <button type="button" 
                                    onclick="openAllowanceModal('{{ $allowance->id }}', '{{ addslashes($allowance->name) }}', '{{ number_format(($allowance->allowance_monthly_amount ?? 500000)/100, 2, ',', '.') }}')" 
                                    class="w-full sm:flex-1 py-2 px-3 rounded-xl text-xs font-black bg-pink-600 hover:bg-pink-500 text-white transition active:scale-95 shadow-md shadow-pink-600/20 flex items-center justify-center gap-1.5">
                                <x-icon name="plus" class="w-3.5 h-3.5" />
                                <span>Carregar Mesada</span>
                            </button>

                            <!-- Botão Registar Despesa da Subconta -->
                            <a href="{{ route('transactions.create', ['account_id' => $allowance->id, 'type' => 'expense']) }}" 
                               class="w-full sm:flex-1 py-2 px-3 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 transition text-center flex items-center justify-center gap-1.5">
                                <x-icon name="expense" class="w-3.5 h-3.5 text-rose-400" />
                                <span>Registar Gasto</span>
                            </a>

                            <!-- Extrato -->
                            <a href="{{ route('accounts.show', $allowance) }}" 
                               class="py-2 px-3 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white transition" title="Ver Extrato Completo">
                                Extrato &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-6 text-center text-slate-500 text-xs">
                        Nenhuma subconta de mesada configurada.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 🏦 CONTAS PRINCIPAIS & FORMULÁRIO -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Accounts Grid (2 cols) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-white">Contas Bancárias & Carteiras Gerais</h3>
                    <span class="text-xs text-slate-400">{{ $accounts->count() }} contas ativas</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse ($accounts as $acc)
                        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between shadow-sm">
                            <div>
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700/60 flex items-center justify-center text-emerald-400">
                                            @if ($acc->type->value === 'checking' || $acc->type->value === 'savings')
                                                <x-icon name="bank" class="w-5 h-5" />
                                            @elseif ($acc->type->value === 'mobile_money')
                                                <x-icon name="mobile" class="w-5 h-5" />
                                            @else
                                                <x-icon name="wallet" class="w-5 h-5" />
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-sm text-white">{{ $acc->name }}</h4>
                                            <p class="text-xs text-slate-400">{{ $acc->bank_name ?? ucfirst($acc->type->value) }}</p>
                                        </div>
                                    </div>

                                    @if (!$acc->is_active)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] bg-slate-800 text-slate-400 border border-slate-700">Inativa</span>
                                    @endif
                                </div>

                                <div class="mt-4">
                                    <span class="text-[10px] text-slate-500 uppercase tracking-wider block">Saldo Disponível</span>
                                    <div class="text-xl font-black {{ $acc->balance >= 0 ? 'text-white' : 'text-rose-400' }}">
                                        {{ formatMoney($acc->balance, $acc->currency ?? 'MZN') }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                                <span>{{ $acc->transactions_count }} transações</span>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('accounts.edit', $acc) }}" class="text-slate-400 hover:text-white font-semibold">
                                        Editar
                                    </a>
                                    <span>&bull;</span>
                                    <a href="{{ route('accounts.show', $acc) }}" class="text-emerald-400 hover:text-emerald-300 font-semibold">
                                        Extrato &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full p-12 text-center text-slate-500 text-xs bg-slate-900 rounded-2xl border border-slate-800">
                            Nenhuma conta principal registada.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Create Account Form (1 col) -->
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 flex flex-col h-fit">
                <h3 class="text-base font-bold text-white mb-4">Nova Conta / Subconta</h3>

                <form method="POST" action="{{ route('accounts.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome da Conta</label>
                        <input type="text" name="name" required placeholder="ex: Millennium BIM, M-Pesa, Carteira" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tipo de Conta</label>
                        <select name="type" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                            @foreach ($accountTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Conta Superior / Pai (Opcional para Subcontas)</label>
                        <select name="parent_id" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Nenhuma (Conta Principal Independente)</option>
                            @foreach ($accounts as $pAcc)
                                <option value="{{ $pAcc->id }}">{{ $pAcc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Instituição / Banco (Opcional)</label>
                        <input type="text" name="bank_name" placeholder="ex: Millennium BIM, Vodacom M-Pesa" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Saldo Inicial (MZN)</label>
                        <input type="text" name="initial_balance" placeholder="ex: 0,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-sm rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="include_in_total" name="include_in_total" value="1" checked class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500">
                        <label for="include_in_total" class="ml-2 text-xs text-slate-300">Incluir no saldo consolidado da família</label>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-emerald-500/20 active:scale-95">
                        Criar Conta
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Atribuição de Mesada -->
    <div id="allowanceModal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center space-x-2.5 mb-1">
                <span class="text-xl">👧</span>
                <h3 class="text-base font-bold text-white">Carregar Mesada do Mês</h3>
            </div>
            <p id="allowanceTargetName" class="text-xs text-pink-400 font-bold mb-4"></p>

            <form id="allowanceForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Debitar de qual Conta Principal?</label>
                    <select name="source_account_id" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} (Saldo: {{ formatMoney($acc->balance, 'MZN') }})</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-500 mt-1">Este valor sairá como uma despesa na categoria Mesadas.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Valor da Mesada (MZN)</label>
                    <input type="text" name="amount" id="allowanceAmountInput" required value="5.000,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-base rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Data da Atribuição</label>
                    <input type="date" name="allocation_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Nota (Opcional)</label>
                    <input type="text" name="notes" placeholder="ex: Mesada de {{ date('F/Y') }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3">
                    <button type="button" onclick="document.getElementById('allowanceModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-black bg-pink-600 hover:bg-pink-500 text-white transition shadow-lg shadow-pink-600/20 active:scale-95">
                        Confirmar Atribuição
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const allowanceRouteTemplate = "{{ route('accounts.allocate_allowance', ':id') }}";
        function openAllowanceModal(subAccountId, subAccountName, defaultAmount) {
            document.getElementById('allowanceTargetName').innerText = subAccountName;
            document.getElementById('allowanceAmountInput').value = defaultAmount || '5.000,00';
            document.getElementById('allowanceForm').action = allowanceRouteTemplate.replace(':id', subAccountId);
            document.getElementById('allowanceModal').classList.remove('hidden');
        }
    </script>
</x-layouts.app>
