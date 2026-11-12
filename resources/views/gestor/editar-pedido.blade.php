@extends('layouts.app')
@section('titulo', 'Editar reserva')
@section('conteudo')
<div class="cartao detalhes-reserva">
    <h2>Editar reserva recusada</h2>
    <p>Podes manter a recusa, aprovar a reserva ou escolher outra sala e horário.</p>

    <form method="POST" action="{{ url('/gestor/pedidos/' . $pedido->id) }}">
        @csrf
        @method('PUT')

        <label for="estado">Decisão</label>
        <select id="estado" name="estado" required>
            <option value="APROVADA" @selected(old('estado', $pedido->estado) === 'APROVADA')>Aprovar reserva</option>
            <option value="RECUSADA" @selected(old('estado', $pedido->estado) === 'RECUSADA')>Manter recusada</option>
        </select>

        <label for="espaco_id">Sala</label>
        <select id="espaco_id" name="espaco_id" required>
            @foreach ($espacos as $espaco)
                <option value="{{ $espaco->id }}" @selected((int) old('espaco_id', $pedido->espaco_id) === $espaco->id)>{{ $espaco->nome_completo }} (capacidade: {{ $espaco->capacidade }})</option>
            @endforeach
        </select>

        <label for="inicio">Início</label>
        <input id="inicio" name="inicio" type="datetime-local" value="{{ old('inicio', $pedido->inicio->format('Y-m-d\\TH:i')) }}" required>

        <label for="fim">Fim</label>
        <input id="fim" name="fim" type="datetime-local" value="{{ old('fim', $pedido->fim->format('Y-m-d\\TH:i')) }}" required>

        <label for="justificacao_decisao">Motivo / observação da decisão</label>
        <textarea id="justificacao_decisao" name="justificacao_decisao" required minlength="3">{{ old('justificacao_decisao', $pedido->justificacao_decisao) }}</textarea>

        @if ($errors->any())
            <p class="erro">{{ $errors->first() }}</p>
        @endif
        <div class="acoes-formulario">
            <a class="botao botao-secundario" href="{{ url('/gestor?estado=RECUSADA') }}">Cancelar</a>
            <button class="botao botao-principal" type="submit">Guardar alteração</button>
        </div>
    </form>
</div>
@endsection
