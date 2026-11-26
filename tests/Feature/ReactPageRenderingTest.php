<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ReactPageRenderingTest extends TestCase
{
    public function test_login_route_serves_the_react_mount_point(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('<div id="app"></div>', false)
            ->assertSee('auth.login');
    }

    public function test_authenticated_user_receives_the_react_reservation_page(): void
    {
        $user = $this->userWithRole('UTILIZADOR');

        $this->actingAs($user)
            ->get('/utilizador/reservar')
            ->assertOk()
            ->assertSee('<div id="app"></div>', false)
            ->assertSee('user.reservation')
            ->assertSee('Utilizador QA');
    }

    public function test_non_admin_cannot_access_the_react_admin_page(): void
    {
        $user = $this->userWithRole('GESTOR_ESPACOS');

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_receives_the_react_admin_page(): void
    {
        $user = $this->userWithRole('ADMINISTRADOR');

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('<div id="app"></div>', false)
            ->assertSee('admin');
    }

    private function userWithRole(string $role): User
    {
        $user = new User;

        $user->forceFill([
            'id' => 1,
            'nome' => 'Utilizador QA',
            'email' => 'qa@example.test',
            'perfil' => $role,
            'ativo' => true,
        ]);

        return $user;
    }
}
