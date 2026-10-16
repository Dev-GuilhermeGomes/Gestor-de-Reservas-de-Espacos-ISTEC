@extends('layouts.app')
@section('titulo', 'Início')
@section('conteudo')
<div class="cartao">
    <h2>Olá, {{ auth()->user()->nome }}</h2>
    <p>Bem-vindo ao Gestor de Reservas de Espaços do ISTEC.</p>
    <p>Usa o menu à esquerda para navegar.</p>
</div>
@endsection