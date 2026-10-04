<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders_successfully(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Iniciar Sessão');
        $response->assertSee('FAMFIN');
    }

    public function test_register_screen_renders_successfully(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Criar Nova Conta');
    }

    public function test_user_can_register_and_authenticate(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Filipe dos Santos',
            'email' => 'filipe.santos@famfin.mz',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'family_name' => 'Família Filipe Teste',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'filipe.santos@famfin.mz',
            'name' => 'Filipe dos Santos',
        ]);

        $this->assertDatabaseHas('families', [
            'name' => 'Família Filipe Teste',
            'currency' => 'MZN',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $family = Family::create([
            'name' => 'Família Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $user = User::create([
            'family_id' => $family->id,
            'name' => 'Filipe',
            'email' => 'filipe@famfin.mz',
            'password' => Hash::make('secret1234'),
            'role' => UserRole::Admin,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'filipe@famfin.mz',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $family = Family::create([
            'name' => 'Família Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        User::create([
            'family_id' => $family->id,
            'name' => 'Filipe',
            'email' => 'filipe@famfin.mz',
            'password' => Hash::make('secret1234'),
            'role' => UserRole::Admin,
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'filipe@famfin.mz',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $family = Family::create([
            'name' => 'Família Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $user = User::create([
            'family_id' => $family->id,
            'name' => 'Filipe',
            'email' => 'filipe@famfin.mz',
            'password' => Hash::make('secret1234'),
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post(route('logout'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_can_update_profile_and_password(): void
    {
        $family = Family::create([
            'name' => 'Família Santos',
            'currency' => 'MZN',
            'locale' => 'pt_MZ',
            'timezone' => 'Africa/Maputo',
        ]);

        $user = User::create([
            'family_id' => $family->id,
            'name' => 'Filipe Antigo',
            'email' => 'filipe.antigo@famfin.mz',
            'password' => Hash::make('oldpassword'),
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($user);

        // Edit screen
        $viewResponse = $this->get(route('profile.edit'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Definições de Perfil & Família', false);

        // Update profile
        $updateResponse = $this->patch(route('profile.update'), [
            'name' => 'Filipe Atualizado',
            'email' => 'filipe.novo@famfin.mz',
            'family_name' => 'Família dos Santos 2026',
            'currency' => 'MZN',
            'timezone' => 'Africa/Maputo',
        ]);

        $updateResponse->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('users', ['email' => 'filipe.novo@famfin.mz', 'name' => 'Filipe Atualizado']);
        $this->assertDatabaseHas('families', ['name' => 'Família dos Santos 2026']);

        // Update password
        $passwordResponse = $this->put(route('profile.password'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $passwordResponse->assertRedirect(route('profile.edit'));
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
}
