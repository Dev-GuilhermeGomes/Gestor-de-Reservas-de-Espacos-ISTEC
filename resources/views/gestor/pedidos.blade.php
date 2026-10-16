@extends('layouts.app')
@section('titulo', 'Pedidos de reserva')
@section('conteudo')
<div class="resumo">
    <div class="cartao"><strong>0</strong>Pedidos pendentes</div>
    <div class="cartao"><strong>0</strong>Aprovadas esta semana</div>
    <div class="cartao"><strong>0</strong>Indisponibilidades ativas</div>
</div>

<div class="cartao">
    <div class="separadores">
        <a class="ativo" href="/gestor">Pendentes</a>
        <span>Aprovadas</span>
        <span>Recusadas</span>
        <span>Todas</span>
    </div>
    <div class="tabela">
        <table>
            <thead>
                <tr><th>Espaço</th><th>Requerente</th><th>Data e hora</th><th>Participantes</th><th>Motivo</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <tr><td colspan="6" class="vazio">Ainda não há pedidos pendentes.</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection