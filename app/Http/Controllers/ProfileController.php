<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();
        $family = $user?->family;

        return view('profile.index', [
            'user' => $user,
            'family' => $family,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'family_name' => 'nullable|string|max:255',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string|max:50',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->family) {
            $user->family->update([
                'name' => $validated['family_name'] ?? $user->family->name,
                'currency' => strtoupper($validated['currency']),
                'timezone' => $validated['timezone'],
            ]);
        }

        return redirect()->route('profile.edit')->with('success', 'Perfil e configurações atualizados com sucesso!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Palavra-passe alterada com sucesso!');
    }
}
