<x-layouts.app>
    <x-slot:header>
        Dívidas, Empréstimos & Financiamentos
    </x-slot:header>

    <!-- Summary KPI -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total em Dívidas Ativas</span>
                <div class="text-2xl font-black text-rose-400 mt-1">
                    {{ $summary['formatted_total_remaining'] }}
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ $summary['active_debts_count'] }} compromissos pendentes</p>
            </div>

            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Já Amortizado</span>
                <div class="text-2xl font-black text-teal-400 mt-1">
                    {{ $summary['formatted_total_paid'] }}
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ $summary['progress_percentage'] }}% do valor inicial liquidado</p>
            </div>

            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Volume Total Contratado</span>
                <div class="text-2xl font-black text-slate-200 mt-1">
                    {{ $summary['formatted_total_original'] }}
                </div>
                <p class="text-xs text-slate-500 mt-1">Valor base acumulado</p>
            </div>
        </div>

        <div class="w-full h-2.5 bg-slate-800 rounded-full overflow-hidden">
            <div class="h-full bg-teal-500 transition-all duration-500" style="width: {{ min(100, $summary['progress_percentage']) }}%"></div>
        </div>
    </div>

    <!-- Debts List & Add Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Debts List (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            @forelse ($debts as $debt)
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400">
                                <x-icon name="debt" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white">{{ $debt->description }}</h3>
                                <p class="text-xs text-slate-400">Credor: <strong class="text-slate-200">{{ $debt->creditor }}</strong> &bull; Tipo: {{ ucfirst($debt->type->value) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            @if ($debt->status->value === 'paid')
                                <span class="text-xs font-bold text-teal-400 bg-teal-500/10 border border-teal-500/20 px-2.5 py-1 rounded-full">
                                    Liquidada
                                </span>
                            @elseif ($debt->status->value === 'overdue')
                                <span class="text-xs font-bold text-rose-400 bg-rose-500/10 border border-rose-500/20 px-2.5 py-1 rounded-full">
                                    Vencida
                                </span>
                            @else
                                <span class="text-xs font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2.5 py-1 rounded-full">
                                    Em Pagamento
                                </span>
                            @endif

                            @if ($debt->status->value !== 'paid')
                                <button onclick="openPaymentModal('{{ $debt->id }}', '{{ addslashes($debt->description) }}')" class="px-3 py-1 rounded-xl text-xs font-semibold bg-emerald-500 text-slate-950 hover:bg-emerald-400 transition">
                                    Amortizar
                                </button>
                            @endif
                        </div>
                    </div>

                    @php
                        $paid = $debt->original_amount - $debt->remaining_amount;
                        $debtProgress = $debt->original_amount > 0 ? round(($paid / $debt->original_amount) * 100, 1) : 0;
                    @endphp

                    <div class="w-full h-2.5 bg-slate-800 rounded-full overflow-hidden my-3">
                        <div class="h-full bg-teal-500" style="width: {{ min(100, $debtProgress) }}%"></div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-400 gap-2">
                        <div>
                            Saldo Devedor: <strong class="text-rose-400">{{ formatMoney($debt->remaining_amount, $family->currency) }}</strong> / Inicial: <span class="text-slate-300">{{ formatMoney($debt->original_amount, $family->currency) }}</span>
                        </div>
                        @if ($debt->monthly_payment)
                            <div>
                                Parcela Mensal: <strong class="text-white">{{ formatMoney($debt->monthly_payment, $family->currency) }}</strong>
                            </div>
                        @endif
                    </div>

                    @if ($debt->payments && count($debt->payments) > 0)
                        <div class="mt-4 pt-3 border-t border-slate-800/80">
                            <p class="text-[11px] font-semibold text-slate-400 mb-2">Últimas Amortizações</p>
                            <div class="space-y-1.5">
                                @foreach ($debt->payments as $p)
                                    <div class="flex justify-between text-xs text-slate-300 bg-slate-800/30 px-3 py-1.5 rounded-lg">
                                        <span>{{ \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y') }} &bull; {{ $p->note ?? 'Amortização' }}</span>
                                        <span class="font-bold text-teal-400">-{{ formatMoney($p->amount, $family->currency) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-12 text-center text-slate-500 text-xs bg-slate-900 rounded-2xl border border-slate-800">
                    Parabéns! Nenhuma dívida ativa registada.
                </div>
            @endforelse
        </div>

        <!-- Create Debt Form (1 col) -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 flex flex-col h-fit">
            <h3 class="text-base font-bold text-white mb-4">Registar Nova Dívida</h3>

            <form method="POST" action="{{ route('debts.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Credor / Instituição</label>
                    <input type="text" name="creditor" required placeholder="ex: Millennium BIM, BCI, Particular" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Descrição</label>
                    <input type="text" name="description" required placeholder="ex: Crédito Automóvel, Empréstimo Pessoal" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tipo</label>
                        <select name="type" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                            @foreach ($debtTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Taxa de Juro (%)</label>
                        <input type="number" step="0.01" name="interest_rate" placeholder="ex: 18.5" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Valor Total (MZN)</label>
                        <input type="text" name="original_amount" required placeholder="ex: 50.000,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Parcela (MZN)</label>
                        <input type="text" name="monthly_payment" placeholder="ex: 3.500,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Data de Vencimento Final (Opcional)</label>
                    <input type="date" name="due_date" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-emerald-500/20">
                    Registar Dívida
                </button>
            </form>
        </div>
    </div>

    <!-- Modal for Debt Payment -->
    <div id="paymentModal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-white mb-1">Registar Amortização de Dívida</h3>
            <p id="paymentDebtName" class="text-xs text-slate-400 mb-4"></p>

            <form id="paymentForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Valor Pago (MZN)</label>
                    <input type="text" name="amount" required placeholder="ex: 2.500,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-sm rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Debitar da Conta (Opcional)</label>
                    <select name="account_id" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Apenas abater saldo devedor (sem debitar conta)</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ formatMoney($acc->balance, 'MZN') }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Data do Pagamento</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Nota</label>
                    <input type="text" name="note" placeholder="ex: Pagamento da parcela do mês" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3">
                    <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-500 text-slate-950 hover:bg-emerald-400 transition">
                        Confirmar Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPaymentModal(debtId, debtName) {
            document.getElementById('paymentDebtName').innerText = 'Dívida: ' + debtName;
            document.getElementById('paymentForm').action = '/debts/' + debtId + '/payment';
            document.getElementById('paymentModal').classList.remove('hidden');
        }
    </script>
</x-layouts.app>
