<x-layouts.app>
    <x-slot:header>
        Metas de Poupança & Reserva de Emergência
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Savings Goals List (2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            @forelse ($goals as $goal)
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                                <x-icon name="piggy-bank" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white">{{ $goal['name'] }}</h3>
                                @if ($goal['deadline'])
                                    <p class="text-xs text-slate-400">Prazo estipulado: {{ $goal['deadline'] }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-full">
                                {{ $goal['progress_percentage'] }}% Atingido
                            </span>
                            <button onclick="openContributeModal('{{ $goal['goal_id'] }}', '{{ addslashes($goal['name']) }}')" class="px-3 py-1 rounded-xl text-xs font-semibold bg-emerald-500 text-slate-950 hover:bg-emerald-400 transition">
                                Contribuir
                            </button>
                        </div>
                    </div>

                    <div class="w-full h-3 bg-slate-800 rounded-full overflow-hidden my-3">
                        <div class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ min(100, $goal['progress_percentage']) }}%"></div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-400 gap-2">
                        <div>
                            Acumulado: <strong class="text-white">{{ $goal['formatted_current'] }}</strong> / Alvo: <span class="text-slate-300">{{ $goal['formatted_target'] }}</span>
                        </div>
                        <div>
                            Falta: <strong class="text-slate-300">{{ $goal['formatted_remaining'] }}</strong>
                        </div>
                    </div>

                    @if ($goal['formatted_monthly_needed'])
                        <div class="mt-4 pt-3 border-t border-slate-800/80 text-xs text-amber-300/90 flex items-center">
                            <x-icon name="calendar" class="w-4 h-4 mr-1.5 flex-shrink-0" />
                            Poupança mensal recomendada para atingir o prazo: <strong class="ml-1 text-white">{{ $goal['formatted_monthly_needed'] }} / mês</strong>
                        </div>
                    @endif

                    @if (isset($goal['model']->contributions) && count($goal['model']->contributions) > 0)
                        <div class="mt-4 pt-3 border-t border-slate-800/80">
                            <p class="text-[11px] font-semibold text-slate-400 mb-2">Últimas Contribuições</p>
                            <div class="space-y-1.5">
                                @foreach ($goal['model']->contributions as $c)
                                    <div class="flex justify-between text-xs text-slate-300 bg-slate-800/30 px-3 py-1.5 rounded-lg">
                                        <span>{{ \Carbon\Carbon::parse($c->contribution_date)->format('d/m/Y') }} &bull; {{ $c->note ?? 'Aporte' }}</span>
                                        <span class="font-bold text-teal-400">+{{ formatMoney($c->amount, $family->currency) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-12 text-center text-slate-500 text-xs bg-slate-900 rounded-2xl border border-slate-800">
                    Nenhuma meta de poupança registada. Crie objetivos como Fundo de Emergência, Viagem, Investimento ou Reforma.
                </div>
            @endforelse
        </div>

        <!-- Create Goal Form (1 col) -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 flex flex-col h-fit">
            <h3 class="text-base font-bold text-white mb-4">Nova Meta de Poupança</h3>

            <form method="POST" action="{{ route('savings.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome do Objetivo</label>
                    <input type="text" name="name" required placeholder="ex: Reserva de Emergência 6 Meses" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Valor Alvo (MZN)</label>
                    <input type="text" name="target_amount" required placeholder="ex: 150.000,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-sm rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Data Limite (Opcional)</label>
                    <input type="date" name="deadline" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Observações (Opcional)</label>
                    <textarea name="notes" rows="2" placeholder="Finalidade da poupança..." class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-emerald-500/20">
                    Criar Meta
                </button>
            </form>
        </div>
    </div>

    <!-- Modal for Contribution -->
    <div id="contributeModal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
            <h3 class="text-base font-bold text-white mb-1">Adicionar Contribuição</h3>
            <p id="contributeGoalName" class="text-xs text-slate-400 mb-4"></p>

            <form id="contributeForm" method="POST" action="" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Valor do Aporte (MZN)</label>
                    <input type="text" name="amount" required placeholder="ex: 5.000,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-sm rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Debitar da Conta (Opcional)</label>
                    <select name="account_id" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Apenas registar aporte (sem debitar conta)</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ formatMoney($acc->balance, 'MZN') }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Data</label>
                    <input type="date" name="contribution_date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Nota</label>
                    <input type="text" name="note" placeholder="ex: Aporte mensal de poupança" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3">
                    <button type="button" onclick="document.getElementById('contributeModal').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-500 text-slate-950 hover:bg-emerald-400 transition">
                        Confirmar Aporte
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openContributeModal(goalId, goalName) {
            document.getElementById('contributeGoalName').innerText = 'Meta: ' + goalName;
            document.getElementById('contributeForm').action = '/savings/' + goalId + '/contribute';
            document.getElementById('contributeModal').classList.remove('hidden');
        }
    </script>
</x-layouts.app>
