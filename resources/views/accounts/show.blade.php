<x-layouts.app>
    <x-slot:header>
        Extrato da Conta: {{ $account->name }}
    </x-slot:header>

    <div class="space-y-6">
        <!-- Account Info Header -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700/60 flex items-center justify-center text-emerald-400 flex-shrink-0">
                    @if ($account->type->value === 'checking' || $account->type->value === 'savings')
                        <x-icon name="bank" class="w-6 h-6" />
                    @elseif ($account->type->value === 'mobile_money')
                        <x-icon name="mobile" class="w-6 h-6" />
                    @else
                        <x-icon name="wallet" class="w-6 h-6" />
                    @endif
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">{{ $account->name }}</h3>
                    <p class="text-xs text-slate-400">
                        {{ $account->bank_name ?? ucfirst($account->type->value) }}
                        @if ($account->account_number)
                            &bull; Nº {{ $account->account_number }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-6">
                <div>
                    <span class="text-[10px] text-slate-500 uppercase tracking-wider block">Saldo Atual</span>
                    <div class="text-2xl font-black {{ $account->balance >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ formatMoney($account->balance, $account->currency ?? 'MZN') }}
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <a href="{{ route('accounts.edit', $account) }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition">
                        <x-icon name="pencil" class="w-4 h-4 mr-1 inline-block" />
                        Editar
                    </a>
                    <a href="{{ route('accounts.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold border border-slate-700 text-slate-400 hover:text-slate-200 transition">
                        Voltar
                    </a>
                </div>
            </div>
        </div>

        <!-- Transactions Statement Table -->
        <div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <h4 class="text-sm font-bold text-white">Histórico de Transações Desta Conta</h4>
                <a href="{{ route('transactions.create') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300">
                    + Registar movimento
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-800/50 border-b border-slate-800 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">Data</th>
                            <th class="px-6 py-3.5">Descrição</th>
                            <th class="px-6 py-3.5">Categoria</th>
                            <th class="px-6 py-3.5 text-right">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse ($transactions as $tx)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-6 py-4 text-xs text-slate-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-200">{{ $tx->description }}</div>
                                    @if ($tx->transferAccount)
                                        <div class="text-[11px] text-indigo-400">Transferência para {{ $tx->transferAccount->name }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-300 whitespace-nowrap">
                                    {{ $tx->category->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-sm whitespace-nowrap {{ $tx->type->value === 'income' ? 'text-teal-400' : ($tx->type->value === 'expense' ? 'text-rose-400' : 'text-slate-200') }}">
                                    {{ $tx->type->value === 'income' ? '+' : ($tx->type->value === 'expense' ? '-' : '') }}{{ formatMoney($tx->amount, $account->currency ?? 'MZN') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500 text-xs">
                                    Nenhuma transação encontrada para esta conta.
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
    </div>
</x-layouts.app>
