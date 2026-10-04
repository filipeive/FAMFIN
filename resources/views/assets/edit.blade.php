<x-layouts.app>
    <x-slot:header>
        Editar Bem Patrimonial
    </x-slot:header>

    <div class="max-w-2xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8">
        <div class="mb-6">
            <h3 class="text-base font-bold text-white">Editar Dados do Bem</h3>
            <p class="text-xs text-slate-400">Atualize a avaliação de mercado e os registos deste ativo.</p>
        </div>

        <form method="POST" action="{{ route('assets.update', $asset) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome do Bem</label>
                <input type="text" name="name" value="{{ old('name', $asset->name) }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tipo de Bem</label>
                <select name="type" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    @foreach ($assetTypes as $type)
                        <option value="{{ $type->value }}" {{ $asset->type->value === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Valor Atual de Mercado (MZN)</label>
                    <input type="text" name="current_value" value="{{ number_format($asset->current_value / 100, 2, ',', '.') }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 font-bold text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Valor de Compra (MZN)</label>
                    <input type="text" name="purchase_value" value="{{ $asset->purchase_value ? number_format($asset->purchase_value / 100, 2, ',', '.') : '' }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Data de Aquisição</label>
                <input type="date" name="purchase_date" value="{{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d') : '' }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Descrição / Notas</label>
                <textarea name="description" rows="3" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">{{ $asset->description }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-5 border-t border-slate-800">
                <form method="POST" action="{{ route('assets.destroy', $asset) }}" onsubmit="return confirm('Deseja realmente eliminar este bem patrimonial?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2.5 rounded-xl border border-rose-500/20 text-rose-400 hover:bg-rose-500/10 text-xs font-semibold transition">
                        Eliminar Bem
                    </button>
                </form>

                <div class="flex items-center space-x-2">
                    <a href="{{ route('assets.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 text-xs font-semibold transition">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition shadow-lg shadow-emerald-500/20">
                        Guardar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
