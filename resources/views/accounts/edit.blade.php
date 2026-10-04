<x-layouts.app>
    <x-slot:header>
        Editar Conta / Carteira
    </x-slot:header>

    <div class="max-w-2xl mx-auto bg-slate-900 rounded-2xl border border-slate-800 p-6 md:p-8">
        <div class="mb-6">
            <h3 class="text-base font-bold text-white">Editar Dados da Conta</h3>
            <p class="text-xs text-slate-400">Atualize os detalhes de identificação e visibilidade da conta.</p>
        </div>

        <form method="POST" action="{{ route('accounts.update', $account) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome da Conta</label>
                <input type="text" name="name" value="{{ old('name', $account->name) }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tipo de Conta</label>
                <select name="type" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    @foreach ($accountTypes as $type)
                        <option value="{{ $type->value }}" {{ $account->type->value === $type->value ? 'selected' : '' }}>{{ ucfirst($type->value) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Instituição / Banco (Opcional)</label>
                <input type="text" name="bank_name" value="{{ old('bank_name', $account->bank_name) }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Número da Conta / NIB (Opcional)</label>
                <input type="text" name="account_number" value="{{ old('account_number', $account->account_number) }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div class="space-y-2 pt-2">
                <div class="flex items-center">
                    <input type="checkbox" id="include_in_total" name="include_in_total" value="1" {{ $account->include_in_total ? 'checked' : '' }} class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500">
                    <label for="include_in_total" class="ml-2 text-xs text-slate-300">Incluir no saldo consolidado da família</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ $account->is_active ? 'checked' : '' }} class="rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500">
                    <label for="is_active" class="ml-2 text-xs text-slate-300">Conta ativa</label>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-5 border-t border-slate-800">
                <a href="{{ route('accounts.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 text-xs font-semibold transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition shadow-lg shadow-emerald-500/20">
                    Guardar Alterações
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
