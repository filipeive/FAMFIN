<x-layouts.app>
    <x-slot:header>
        Editar Categoria
    </x-slot:header>

    <div class="max-w-xl mx-auto bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8">
        <div class="mb-6">
            <h3 class="text-base font-bold text-white">Editar Dados da Categoria</h3>
            <p class="text-xs text-slate-400">Altere o nome, vínculo hierárquico ou cor desta classificação.</p>
        </div>

        <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome da Categoria</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Tipo</label>
                <select name="type" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="expense" {{ $category->type->value === 'expense' ? 'selected' : '' }}>Despesa</option>
                    <option value="income" {{ $category->type->value === 'income' ? 'selected' : '' }}>Receita</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Categoria Superior (Pai)</label>
                <select name="parent_id" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Nenhuma (Categoria Principal)</option>
                    @foreach ($parentCategories as $pCat)
                        <option value="{{ $pCat->id }}" {{ $category->parent_id === $pCat->id ? 'selected' : '' }}>{{ $pCat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5">Cor de Destaque</label>
                <div class="flex items-center space-x-2">
                    <input type="color" name="color" value="{{ old('color', $category->color ?? '#10B981') }}" class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 cursor-pointer p-1">
                    <span class="text-xs text-slate-400">Identificação visual</span>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-5 border-t border-slate-800">
                <a href="{{ route('categories.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 text-xs font-semibold transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition shadow-lg shadow-emerald-500/20">
                    Guardar Alterações
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
