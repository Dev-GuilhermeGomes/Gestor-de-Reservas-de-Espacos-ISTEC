<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ReservationSubmissionTest extends TestCase
{
    public function test_guest_request_for_reservation_form_redirects_to_login(): void
    {
        $this->get('/utilizador/reservar')
            ->assertRedirectToRoute('login');
    }

    public function test_authenticated_user_gets_validation_errors_when_submission_fields_are_missing(): void
    {
        $user = (new User)->forceFill([
            'id' => 1,
            'nome' => 'Utilizador QA',
            'email' => 'qa@example.test',
            'perfil' => 'UTILIZADOR',
            'ativo' => true,
        ]);

        $this->actingAs($user)
            ->post('/utilizador/reservar')
            ->assertInvalid(['espaco_id', 'inicio', 'fim', 'participantes', 'motivo']);
    }
}
