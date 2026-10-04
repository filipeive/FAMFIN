<x-layouts.app>
    <x-slot:header>
        Definições de Perfil & Família
    </x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Profile & Family Info Form -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <x-icon name="user" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Dados Pessoais & Configuração da Família</h3>
                    <p class="text-xs text-slate-400">Atualize o seu nome, contacto e preferências monetárias.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome do Utilizador</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Endereço de E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nome da Família / Tenant</label>
                        <input type="text" name="family_name" value="{{ old('family_name', $family?->name) }}" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Moeda Padrão</label>
                        <select name="currency" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="MZN" {{ ($family?->currency ?? 'MZN') === 'MZN' ? 'selected' : '' }}>MZN — Metical (Moçambique)</option>
                            <option value="USD" {{ ($family?->currency ?? '') === 'USD' ? 'selected' : '' }}>USD — Dólar Americano</option>
                            <option value="EUR" {{ ($family?->currency ?? '') === 'EUR' ? 'selected' : '' }}>EUR — Euro</option>
                            <option value="ZAR" {{ ($family?->currency ?? '') === 'ZAR' ? 'selected' : '' }}>ZAR — Rand Sul-Africano</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Fuso Horário</label>
                        <input type="text" name="timezone" value="{{ old('timezone', $family?->timezone ?? 'Africa/Maputo') }}" required class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex justify-end pt-3">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-emerald-500/20">
                        Guardar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Change Form -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8">
            <div class="flex items-center space-x-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                    <x-icon name="lock" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Alterar Palavra-passe</h3>
                    <p class="text-xs text-slate-400">Mantenha a sua conta protegida com uma credencial segura.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">Palavra-passe Atual</label>
                    <input type="password" name="current_password" required placeholder="••••••••" class="w-full sm:w-1/2 bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Nova Palavra-passe</label>
                        <input type="password" name="password" required placeholder="Mínimo 8 caracteres" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1.5">Confirmar Nova Palavra-passe</label>
                        <input type="password" name="password_confirmation" required placeholder="Repetir nova palavra-passe" class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-xs rounded-xl px-4 py-3 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex justify-end pt-3">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-bold text-xs transition">
                        Atualizar Palavra-passe
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
