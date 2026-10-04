<x-layouts.app>
    <x-slot:header>
        Contas & Carteiras
    </x-slot:header>

    <!-- Header Actions & Total Balance -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 p-6 rounded-2xl border border-slate-800">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Saldo Consolidado em Contas</span>
            <div class="text-3xl font-black text-emerald-400 mt-1">
                {{ $formattedTotalBalance }}
            </div>
            <p class="text-xs text-slate-400 mt-1">Soma das contas ativas que integram o saldo geral</p>
        </div>

        <div class="flex items-center space-x-3">
            <form method="POST" action="{{ route('accounts.recalculate') }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition">
                    <x-icon name="refresh" class="w-4 h-4 mr-2" />
                    Recalcular Saldos
                </button>
            </form>
        </div>
    </div>

    <!-- Accounts List & Create Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Accounts Grid (2 cols) -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse ($accounts as $acc)
                <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between">
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
                        <a href="{{ route('transactions.index', ['account_id' => $acc->id]) }}" class="text-emerald-400 hover:text-emerald-300 font-semibold">
                            Extrato &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-2 p-12 text-center text-slate-500 text-xs bg-slate-900 rounded-2xl border border-slate-800">
                    Nenhuma conta cadastrada.
                </div>
            @endforelse
        </div>

        <!-- Create Account Form (1 col) -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6 flex flex-col h-fit">
            <h3 class="text-base font-bold text-white mb-4">Nova Conta / Carteira</h3>

            <form method="POST" action="{{ route('accounts.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome da Conta</label>
                    <input type="text" name="name" required placeholder="ex: Millennium BIM Filipe, M-Pesa, Carteira" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tipo de Conta</label>
                    <select name="type" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach ($accountTypes as $type)
                            <option value="{{ $type->value }}">{{ ucfirst($type->value) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Instituição / Banco (Opcional)</label>
                    <input type="text" name="bank_name" placeholder="ex: Millennium BIM, Vodacom M-Pesa, Standard Bank" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Saldo Inicial (MZN)</label>
                    <input type="text" name="initial_balance" placeholder="ex: 25.000,00" class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-sm rounded-xl px-3.5 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="include_in_total" name="include_in_total" value="1" checked class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500">
                    <label for="include_in_total" class="ml-2 text-xs text-slate-300">Incluir no saldo consolidado da família</label>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-emerald-500/20">
                    Criar Conta
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
