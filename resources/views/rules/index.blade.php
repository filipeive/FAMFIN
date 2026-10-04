<x-layouts.app>
    <x-slot:header>
        Regras Financeiras & Simulador de Deduções
    </x-slot:header>

    <!-- Top Info Card (Mozambican Rules) -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="flex items-start space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 flex-shrink-0">
                <x-icon name="rules" class="w-6 h-6" />
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Motor de Regras Financeiras (Moçambique 🇲🇿)</h3>
                <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                    O FAMFIN aplica automaticamente as regras de dedução do modelo de 57 meses do usuário:
                    <strong>INSS (3%)</strong>, <strong>Dízimo (10%)</strong> e cálculo de <strong>Renda Líquida Disponível (87%)</strong> sobre as receitas brutas para assegurar a consistência do orçamento familiar.
                </p>
            </div>
        </div>
    </div>

    <!-- Simulator & Rules Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Interactive Salary Deduction Simulator -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <x-icon name="calculator" class="w-5 h-5 text-emerald-400" />
                    <h3 class="text-base font-bold text-white">Simulador de Deduções e Salário Líquido</h3>
                </div>

                <form method="GET" action="{{ route('rules.index') }}" class="mb-6">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Salário Bruto / Receita Prevista (MZN)</label>
                    <div class="flex space-x-2">
                        <input type="text" name="simulate_gross" value="{{ $simulatedGross ?? '50000' }}" placeholder="ex: 50.000,00" class="flex-1 bg-slate-800 border border-slate-700 text-slate-100 font-bold text-base rounded-xl px-4 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-emerald-500/20">
                            Simular
                        </button>
                    </div>
                </form>

                @if ($simulatedBreakdown)
                    <div class="space-y-3 bg-slate-800/40 p-5 rounded-xl border border-slate-700/50">
                        <div class="flex justify-between items-center text-sm border-b border-slate-700/60 pb-2">
                            <span class="text-slate-400 font-medium">Salário Bruto Informado:</span>
                            <span class="font-bold text-white">{{ $simulatedBreakdown['formatted_gross'] }}</span>
                        </div>

                        <div class="space-y-2 py-1">
                            @foreach ($simulatedBreakdown['deductions'] as $d)
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-400 flex items-center">
                                        <x-icon name="trending-down" class="w-3.5 h-3.5 text-rose-400 mr-1.5" />
                                        {{ $d['name'] }} ({{ $d['percentage'] }}%)
                                    </span>
                                    <span class="font-semibold text-rose-400">-{{ formatMoney($d['amount'], $family->currency) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-between items-center text-xs border-t border-slate-700/60 pt-2 text-slate-400">
                            <span>Total de Deduções:</span>
                            <span class="font-bold text-rose-400">-{{ $simulatedBreakdown['formatted_deductions'] }}</span>
                        </div>

                        <div class="flex justify-between items-center text-base border-t border-slate-700 pt-3">
                            <span class="font-bold text-slate-200">Renda Líquida Disponível:</span>
                            <span class="font-black text-emerald-400 text-lg">{{ $simulatedBreakdown['formatted_net'] }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Rules Management List -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <x-icon name="adjustments" class="w-5 h-5 text-emerald-400" />
                        <h3 class="text-base font-bold text-white">Regras Cadastradas</h3>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse ($rules as $rule)
                        <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-700/50 flex items-center justify-between">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h4 class="font-bold text-sm text-slate-200">{{ $rule->name }}</h4>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $rule->percentage }}%
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Tipo: {{ ucfirst($rule->type->value) }} &bull; Base: {{ $rule->config['base'] ?? 'gross' }}
                                </p>
                            </div>

                            <form method="POST" action="{{ route('rules.toggle', $rule) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ $rule->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-slate-800 text-slate-500 border border-slate-700' }}">
                                    {{ $rule->is_active ? 'Ativa' : 'Inativa' }}
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-500 text-xs">
                            Nenhuma regra financeira configurada.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Create Rule Form -->
            <div class="mt-6 pt-4 border-t border-slate-800">
                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">Adicionar Nova Regra</h4>
                <form method="POST" action="{{ route('rules.store') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input type="text" name="name" required placeholder="Nome da regra" class="bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <select name="type" required class="bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            @foreach ($ruleTypes as $rt)
                                <option value="{{ $rt->value }}">{{ ucfirst($rt->value) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input type="number" step="0.01" name="percentage" required placeholder="Percentagem (%)" class="bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <select name="base" class="bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="gross">Base Bruta</option>
                            <option value="net_after_inss">Líquido pós-INSS</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-semibold text-xs transition">
                        Criar Regra
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
