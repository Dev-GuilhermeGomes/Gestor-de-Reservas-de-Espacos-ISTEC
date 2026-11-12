<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GestorController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(auth()->check() ? '/painel' : '/login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'mostrar'])->name('login');
    Route::post('/login', [AuthController::class, 'entrar'])->middleware('throttle:5,1');
}); // o throttle limita o número de tentativas de login para 5 por minuto, ajudando a prevenir ataques de força bruta.

Route::middleware('throttle:5,1')->group(function () {
    Route::get('/two-factor/setup', [AuthController::class, 'mostrarConfiguracao']);
    Route::post('/two-factor/setup', [AuthController::class, 'configurar']);
    Route::get('/two-factor/challenge', [AuthController::class, 'mostrarDesafio']);
    Route::post('/two-factor/challenge', [AuthController::class, 'verificar']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'sair']);
    Route::view('/painel', 'painel');
    Route::get('/gestor', [GestorController::class, 'pedidos'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::get('/gestor/ocupacao', [GestorController::class, 'ocupacao'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::get('/gestor/indisponibilidades', [GestorController::class, 'indisponibilidades'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::get('/gestor/indisponibilidades/criar', [GestorController::class, 'criarIndisponibilidade'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::post('/gestor/indisponibilidades', [GestorController::class, 'guardarIndisponibilidade'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::delete('/gestor/indisponibilidades/{indisponibilidade}', [GestorController::class, 'apagarIndisponibilidade'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::get('/gestor/indisponibilidades/{indisponibilidade}/editar', [GestorController::class, 'editarIndisponibilidade'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::put('/gestor/indisponibilidades/{indisponibilidade}', [GestorController::class, 'atualizarIndisponibilidade'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::get('/gestor/pedidos/{pedido}/recusar', [GestorController::class, 'mostrarRecusa'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::post('/gestor/pedidos/{pedido}/recusar', [GestorController::class, 'recusar'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::get('/gestor/pedidos/{pedido}/motivo', [GestorController::class, 'mostrarMotivo'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::get('/gestor/pedidos/{pedido}/editar', [GestorController::class, 'editarPedido'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::put('/gestor/pedidos/{pedido}', [GestorController::class, 'atualizarPedido'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::get('/gestor/salas', [GestorController::class, 'salas'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::get('/gestor/salas/criar', [GestorController::class, 'criarSalaForm'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::post('/gestor/salas', [GestorController::class, 'criarSala'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::get('/gestor/salas/{espaco}/editar', [GestorController::class, 'editarSala'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::put('/gestor/salas/{espaco}', [GestorController::class, 'atualizarSala'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::post('/gestor/salas/{espaco}/equipamentos', [GestorController::class, 'adicionarEquipamento'])
        ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::view('/admin', 'area', ['titulo' => 'Área da Administração'])
        ->middleware('perfil:ADMINISTRADOR');
});
