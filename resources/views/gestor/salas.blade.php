@extends('layouts.app')
@section('titulo', 'Salas')
@section('conteudo')
@if (session('sucesso'))
    <p class="sucesso">{{ session('sucesso') }}</p>
@endif
@if ($errors->any())
    <p class="erro">{{ $errors->first() }}</p>
@endif

<div class="cartao lista-salas">
    <div class="cabecalho-cartao">
        <h2 class="titulo-cartao">Salas disponíveis</h2>
        <a class="botao botao-principal" href="{{ url('/gestor/salas/criar') }}">Nova sala</a>
    </div>
    <div class="tabela">
        <table>
            <thead><tr><th>Espaço</th><th>Tipo</th><th>Capacidade</th><th>Piso</th><th>Equipamentos</th><th>Ações</th></tr></thead>
            <tbody>
                @forelse ($espacos as $espaco)
                    <tr>
                        <td>{{ $espaco->nome_completo }}</td>
                        <td>{{ $espaco->tipo }}</td>
                        <td>{{ $espaco->capacidade }}</td>
                        <td>{{ $espaco->piso ?? '-' }}</td>
                        <td>{{ $espaco->equipamentos->pluck('nome')->join(', ') ?: '-' }}</td>
                        <td><a class="botao botao-secundario" href="{{ url('/gestor/salas/' . $espaco->id . '/editar') }}">Editar</a></td>
                    </tr>
                @empty
                    <tr><td class="vazio" colspan="6">Ainda não existem salas disponíveis.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
