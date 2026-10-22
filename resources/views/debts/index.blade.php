<x-layouts.app>
    <x-slot:header>
        Dívidas, Empréstimos & Financiamentos
    </x-slot:header>

    <!-- Summary KPI -->
    <div class="p-4 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-6 mb-4">
            <div>
                <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Dívidas Ativas</span>
                <div class="text-xl sm:text-2xl font-black text-rose-400 mt-1">
                    {{ $summary['formatted_total_remaining'] }}
                </div>
                <p class="text-[10px] sm:text-xs text-slate-500 mt-0.5">{{ $summary['active_debts_count'] }} compromissos</p>
            </div>

            <div>
                <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Amortizado</span>
                <div class="text-xl sm:text-2xl font-black text-teal-400 mt-1">
                    {{ $summary['formatted_total_paid'] }}
                </div>
                <p class="text-[10px] sm:text-xs text-slate-500 mt-0.5">{{ $summary['progress_percentage'] }}% liquidado</p>
            </div>

            <div class="col-span-2 sm:col-span-1">
                <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-400">Total Contratado</span>
                <div class="text-xl sm:text-2xl font-black text-slate-200 mt-1">
                    {{ $summary['formatted_total_original'] }}
                </div>
                <p class="text-[10px] sm:text-xs text-slate-500 mt-0.5">Valor base acumulado</p>
            </div>
        </div>

        <div class="w-full h-2.5 bg-slate-800 rounded-full overflow-hidden">
            <div class="h-full bg-teal-500 transition-all duration-500" style="width: {{ min(100, $summary['progress_percentage']) }}%"></div>
        </div>
    </div>

    <!-- Debts Grid & Add Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Debts Grid (2 cols) -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse ($debts as $debt)
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between shadow-sm">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex items-center space-x-2.5 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 flex-shrink-0">
                                    <x-icon name="debt" class="w-4 h-4" />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-bold text-white truncate">{{ $debt->description }}</h3>
                                    <p class="text-[10px] text-slate-400 truncate">Credor: <strong class="text-slate-200">{{ $debt->creditor }}</strong></p>
                                </div>
                            </div>

                            <div>
                                @if ($debt->status->value === 'paid')
                                    <span class="text-[10px] font-bold text-teal-400 bg-teal-500/10 border border-teal-500/20 px-2 py-0.5 rounded-full">
                                        Quitada
                                    </span>
                                @elseif ($debt->status->value === 'overdue')
                                    <span class="text-[10px] font-bold text-rose-400 bg-rose-500/10 border border-rose-500/20 px-2 py-0.5 rounded-full">
                                        Vencida
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-full">
                                        Ativa
                                    </span>
                                @endif
                            </div>
                        </div>

                        @php
                            $paid = $debt->original_amount - $debt->remaining_amount;
                            $debtProgress = $debt->original_amount > 0 ? round(($paid / $debt->original_amount) * 100, 1) : 0;
                        @endphp

                        <div class="my-2.5">
                            <div class="flex justify-between text-[11px] mb-1">
                                <span class="text-slate-400">Amortizado</span>
                                <span class="font-bold text-teal-400">{{ $debtProgress }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-teal-500" style="width: {{ min(100, $debtProgress) }}%"></div>
                            </div>
                        </div>

                        <div class="space-y-1 text-xs text-slate-400 pt-1">
                            <div class="flex justify-between">
                                <span>Saldo Devedor:</span>
                                <strong class="text-rose-400">{{ formatMoney($debt->remaining_amount, $family->currency) }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Inicial:</span>
                                <span class="text-slate-300">{{ formatMoney($debt->original_amount, $family->currency) }}</span>
                            </div>
                            @if ($debt->monthly_payment)
                                <div class="flex justify-between">
                                    <span>Parcela Mensal:</span>
                                    <strong class="text-white">{{ formatMoney($debt->monthly_payment, $family->currency) }}</strong>
                                </div>
                            @endif
                        </div>

                        @if ($debt->payments && count($debt->payments) > 0)
                            <div class="mt-3 pt-2.5 border-t border-slate-800/80">
                                <p class="text-[10px] font-bold uppercase text-slate-400 mb-1.5">Últimas Amortizações</p>
                                <div class="space-y-1">
                                    @foreach (array_slice($debt->payments->all(), 0, 2) as $p)
                                        <div class="flex justify-between text-[11px] text-slate-300 bg-slate-800/40 px-2.5 py-1 rounded-lg">
                                            <span class="truncate max-w-[120px]">{{ \Carbon\Carbon::parse($p->payment_date)->format('d/m') }} &bull; {{ $p->note ?? 'Amortização' }}</span>
                                            <span class="font-bold text-teal-400">-{{ formatMoney($p->amount, $family->currency) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($debt->status->value !== 'paid')
                        <div class="mt-4 pt-3 border-t border-slate-800">
                            <button onclick="openPaymentModal('{{ $debt->id }}', '{{ addslashes($debt->description) }}')" class="w-full py-2 rounded-xl text-xs font-bold bg-emerald-500 text-slate-950 hover:bg-emerald-400 transition active:scale-95 shadow-md shadow-emerald-500/10">
                                Registar Amortização
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-full p-12 text-center text-slate-500 text-xs bg-slate-900 rounded-2xl border border-slate-800">
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
