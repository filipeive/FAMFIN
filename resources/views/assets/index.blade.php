<x-layouts.app>
    <x-slot:header>
        Património & Bens
    </x-slot:header>

    <!-- Net Worth Asset Summary -->
    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Valor Total de Bens</span>
                <div class="text-3xl font-black text-emerald-400 mt-1">
                    {{ $formattedTotalCurrentValue }}
                </div>
                <p class="text-xs text-slate-500 mt-1">Avaliação atual acumulada do património</p>
            </div>

            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total de Aquisição</span>
                <div class="text-2xl font-bold text-slate-200 mt-1">
                    {{ $formattedTotalPurchaseValue }}
                </div>
                <p class="text-xs text-slate-500 mt-1">Custo histórico investido</p>
            </div>

            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Valorização / Ganho Líquido</span>
                <div class="text-2xl font-black {{ $totalAppreciation >= 0 ? 'text-teal-400' : 'text-rose-400' }} mt-1">
                    {{ $totalAppreciation >= 0 ? '+' : '' }}{{ $formattedTotalAppreciation }}
                </div>
                <p class="text-xs text-slate-500 mt-1">Variação patrimonial acumulada</p>
            </div>
        </div>
    </div>

    <!-- Assets Grid & Create Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Assets Cards (2 cols) -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse ($assets as $asset)
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700/60 flex items-center justify-center text-emerald-400">
                                    <x-icon name="assets" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-white">{{ $asset->name }}</h4>
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700 mt-0.5">
                                        {{ $asset->type->label() }}
                                    </span>
                                </div>
                            </div>

                            <a href="{{ route('assets.edit', $asset) }}" title="Editar" class="p-1 rounded-lg text-slate-500 hover:text-emerald-400 hover:bg-emerald-500/10 transition">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </a>
                        </div>

                        <div class="mt-4">
                            <span class="text-[10px] text-slate-500 uppercase tracking-wider block">Valor de Mercado Atual</span>
                            <div class="text-xl font-black text-emerald-400">
                                {{ formatMoney($asset->current_value, $family->currency) }}
                            </div>
                        </div>

                        @if ($asset->purchase_value)
                            <div class="mt-2 text-xs text-slate-400 flex justify-between">
                                <span>Aquisição: {{ formatMoney($asset->purchase_value, $family->currency) }}</span>
                                @if ($asset->appreciation !== null)
                                    <span class="font-bold {{ $asset->appreciation >= 0 ? 'text-teal-400' : 'text-rose-400' }}">
                                        {{ $asset->appreciation >= 0 ? '+' : '' }}{{ formatMoney($asset->appreciation, $family->currency) }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if ($asset->description)
                            <p class="text-xs text-slate-500 mt-2 line-clamp-2">{{ $asset->description }}</p>
                        @endif
                    </div>

                    @if ($asset->purchase_date)
                        <div class="mt-4 pt-3 border-t border-slate-800/80 text-[11px] text-slate-500">
                            Adquirido em: {{ \Carbon\Carbon::parse($asset->purchase_date)->format('d/m/Y') }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-2 p-12 text-center text-slate-500 text-xs bg-slate-900 rounded-2xl border border-slate-800">
                    Nenhum bem patrimonial registado. Adicione imóveis, veículos, equipamentos ou participações de investimento.
                </div>
            @endforelse
        </div>

        <!-- Create Asset Form (1 col) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 h-fit">
            <h3 class="text-base font-bold text-white mb-4">Registar Novo Bem</h3>

            <form method="POST" action="{{ route('assets.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome do Bem</label>
                    <input type="text" name="name" required placeholder="ex: Apartamento Maputo, Toyota Hilux, Terreno" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tipo de Bem</label>
                    <select name="type" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach ($assetTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Valor Atual de Mercado (MZN)</label>
                    <input type="text" name="current_value" required placeholder="ex: 1.500.000,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Valor de Compra / Aquisição (MZN)</label>
                    <input type="text" name="purchase_value" placeholder="ex: 1.200.000,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Data de Aquisição (Opcional)</label>
                    <input type="date" name="purchase_date" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Descrição / Notas</label>
                    <textarea name="description" rows="2" placeholder="Localização, matrícula ou especificações..." class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-emerald-500/20 mt-2">
                    Registar Bem
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
