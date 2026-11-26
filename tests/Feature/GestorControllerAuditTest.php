<?php

namespace Tests\Feature;

use App\Models\Espaco;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Notification as NotificationInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GestorControllerAuditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('utilizadores', function (Blueprint $table): void {
            $table->id();
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('perfil');
            $table->boolean('ativo')->default(true);
            $table->text('two_factor_secret')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('espacos', function (Blueprint $table): void {
            $table->id();
            $table->string('edificio');
            $table->string('nome');
            $table->string('tipo');
            $table->unsignedInteger('capacidade');
            $table->unsignedInteger('piso')->nullable();
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('espaco_gestores', function (Blueprint $table): void {
            $table->unsignedBigInteger('espaco_id');
            $table->unsignedBigInteger('utilizador_id');
        });

        Schema::create('reservas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('utilizador_id');
            $table->unsignedBigInteger('espaco_id');
            $table->unsignedBigInteger('tipo_utilizacao_id')->nullable();
            $table->timestamp('inicio');
            $table->timestamp('fim');
            $table->unsignedInteger('participantes')->default(1);
            $table->text('motivo')->nullable();
            $table->string('estado');
            $table->unsignedBigInteger('decidido_por')->nullable();
            $table->timestamp('decidido_em')->nullable();
            $table->text('justificacao_decisao')->nullable();
            $table->timestamps();
        });

        Schema::create('indisponibilidades', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('espaco_id');
            $table->timestamp('inicio');
            $table->timestamp('fim');
            $table->string('motivo');
            $table->unsignedBigInteger('criado_por')->nullable();
            $table->timestamps();
        });

        Schema::create('auditoria', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reserva_id')->nullable();
            $table->unsignedBigInteger('utilizador_id')->nullable();
            $table->string('acao')->nullable();
            $table->timestamps();
        });
    }

    public function test_refusal_rejects_a_reservation_that_is_not_approved(): void
    {
        ['manager' => $manager, 'reservation' => $reservation] = $this->createDecisionContext('RECUSADA');

        $response = $this->actingAs($manager)
            ->post('/gestor/pedidos/'.$reservation->id.'/recusar', [
                'justificacao_decisao' => 'Motivo atualizado',
            ]);

        $this->assertDatabaseHas('reservas', [
            'id' => $reservation->id,
            'estado' => 'RECUSADA',
        ]);
        $response->assertNotFound();
    }

    public function test_refused_reservation_cannot_be_approved_again(): void
    {
        ['manager' => $manager, 'space' => $space, 'reservation' => $reservation] = $this->createDecisionContext('RECUSADA');

        $response = $this->actingAs($manager)
            ->put('/gestor/pedidos/'.$reservation->id, [
                'espaco_id' => $space->id,
                'inicio' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'fim' => now()->addDays(2)->addHour()->format('Y-m-d H:i:s'),
                'estado' => 'APROVADA',
                'justificacao_decisao' => 'Revisão da decisão',
            ]);

        $this->assertDatabaseHas('reservas', [
            'id' => $reservation->id,
            'estado' => 'RECUSADA',
        ]);
        $response->assertNotFound();
    }

    public function test_pending_reservation_can_be_approved(): void
    {
        ['manager' => $manager, 'space' => $space, 'reservation' => $reservation] = $this->createDecisionContext('PENDENTE');

        $response = $this->actingAs($manager)
            ->put('/gestor/pedidos/'.$reservation->id, [
                'espaco_id' => $space->id,
                'inicio' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'fim' => now()->addDays(2)->addHour()->format('Y-m-d H:i:s'),
                'estado' => 'APROVADA',
                'justificacao_decisao' => 'Reserva aprovada',
            ]);

        $this->assertDatabaseHas('reservas', [
            'id' => $reservation->id,
            'estado' => 'APROVADA',
        ]);
        $response->assertRedirect('/gestor?estado=APROVADA');
    }

    public function test_approving_a_past_reservation_is_rejected(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 25)->setTime(12, 0));
        ['manager' => $manager, 'space' => $space, 'reservation' => $reservation] = $this->createDecisionContext('RECUSADA');

        $this->actingAs($manager)
            ->put('/gestor/pedidos/'.$reservation->id, [
                'espaco_id' => $space->id,
                'inicio' => now()->subDay()->format('Y-m-d H:i:s'),
                'fim' => now()->subDay()->addHour()->format('Y-m-d H:i:s'),
                'estado' => 'APROVADA',
                'justificacao_decisao' => 'Reserva aprovada',
            ])
            ->assertSessionHasErrors('inicio');
    }

    public function test_decision_sends_a_notification_to_the_requester(): void
    {
        Notification::fake();
        ['manager' => $manager, 'requester' => $requester, 'reservation' => $reservation] = $this->createDecisionContext();

        $this->actingAs($manager)
            ->post('/gestor/pedidos/'.$reservation->id.'/recusar', [
                'justificacao_decisao' => 'Sala indisponível',
            ]);

        Notification::assertSentTo(
            $requester,
            function (NotificationInstance $notification, array $channels): bool {
                return true;
            },
        );
    }

    public function test_decision_creates_an_audit_record(): void
    {
        ['manager' => $manager, 'reservation' => $reservation] = $this->createDecisionContext();

        $this->actingAs($manager)
            ->post('/gestor/pedidos/'.$reservation->id.'/recusar', [
                'justificacao_decisao' => 'Sala indisponível',
            ]);

        $this->assertDatabaseCount('auditoria', 1);
    }

    public function test_manager_requests_route_serves_the_react_page(): void
    {
        ['manager' => $manager] = $this->createDecisionContext();

        $this->actingAs($manager)
            ->get('/gestor')
            ->assertOk()
            ->assertSee('<div id="app"></div>', false)
            ->assertSee('manager.requests');
    }

    public function test_decision_rolls_back_when_audit_persistence_fails(): void
    {
        ['manager' => $manager, 'reservation' => $reservation] = $this->createDecisionContext();
        DB::unprepared("CREATE TRIGGER fail_audit_insert BEFORE INSERT ON auditoria BEGIN SELECT RAISE(ABORT, 'audit failure'); END");

        $this->actingAs($manager)
            ->post('/gestor/pedidos/'.$reservation->id.'/recusar', [
                'justificacao_decisao' => 'Sala indisponível',
            ]);

        $this->assertDatabaseHas('reservas', [
            'id' => $reservation->id,
            'estado' => 'APROVADA',
        ]);
    }

    public function test_deleting_unavailability_preserves_its_history(): void
    {
        ['manager' => $manager, 'space' => $space] = $this->createDecisionContext();
        $id = DB::table('indisponibilidades')->insertGetId([
            'espaco_id' => $space->id,
            'inicio' => now()->addDay(),
            'fim' => now()->addDays(2),
            'motivo' => 'Manutenção',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($manager)
            ->delete('/gestor/indisponibilidades/'.$id);

        $this->assertDatabaseHas('indisponibilidades', ['id' => $id]);
    }

    public function test_marking_unavailability_available_preserves_its_history(): void
    {
        ['manager' => $manager, 'space' => $space] = $this->createDecisionContext();
        $id = DB::table('indisponibilidades')->insertGetId([
            'espaco_id' => $space->id,
            'inicio' => now()->addDay(),
            'fim' => now()->addDays(2),
            'motivo' => 'Manutenção',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($manager)
            ->put('/gestor/indisponibilidades/'.$id, [
                'espaco_id' => $space->id,
                'inicio' => now()->addDay()->format('Y-m-d H:i:s'),
                'fim' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'estado' => 'DISPONIVEL',
            ]);

        $this->assertDatabaseHas('indisponibilidades', ['id' => $id]);
    }

    /**
     * @return array{manager: User, requester: User, space: Espaco, reservation: Pedido}
     */
    private function createDecisionContext(string $state = 'APROVADA'): array
    {
        $manager = User::create([
            'nome' => 'Gestor Teste',
            'email' => 'gestor@example.test',
            'password' => 'password',
            'perfil' => 'GESTOR_ESPACOS',
            'ativo' => true,
        ]);

        $requester = User::create([
            'nome' => 'Utilizador Teste',
            'email' => 'utilizador@example.test',
            'password' => 'password',
            'perfil' => 'UTILIZADOR',
            'ativo' => true,
        ]);

        $space = Espaco::create([
            'edificio' => 'ISTEC',
            'nome' => 'Sala QA',
            'tipo' => 'SALA',
            'capacidade' => 30,
            'ativo' => true,
        ]);
        $space->gestores()->attach($manager->id);

        $reservation = Pedido::create([
            'utilizador_id' => $requester->id,
            'espaco_id' => $space->id,
            'inicio' => now()->addDays(2),
            'fim' => now()->addDays(2)->addHour(),
            'participantes' => 2,
            'motivo' => 'Teste de reserva',
            'estado' => $state,
        ]);

        return compact('manager', 'requester', 'space', 'reservation');
    }
}
