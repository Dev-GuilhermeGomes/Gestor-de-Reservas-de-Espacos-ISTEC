@extends('layouts.app')
@section('titulo', 'Editar indisponibilidade')
@section('conteudo')
<div class="cartao detalhes-reserva">
    <h2>Editar indisponibilidade</h2>
    <form method="POST" action="{{ url('/gestor/indisponibilidades/' . $indisponibilidade->id) }}">
        @csrf
        @method('PUT')
        <label for="espaco_id">Espaço</label>
        <select id="espaco_id" name="espaco_id" required>
            @foreach ($espacos as $espaco)
                <option value="{{ $espaco->id }}" @selected((int) old('espaco_id', $indisponibilidade->espaco_id) === $espaco->id)>{{ $espaco->nome_completo }}</option>
            @endforeach
        </select>

        <label for="inicio">Início</label>
        <input id="inicio" name="inicio" type="datetime-local" value="{{ old('inicio', \Carbon\Carbon::parse($indisponibilidade->inicio)->format('Y-m-d\\TH:i')) }}" required>

        <label for="fim">Fim</label>
        <input id="fim" name="fim" type="datetime-local" value="{{ old('fim', \Carbon\Carbon::parse($indisponibilidade->fim)->format('Y-m-d\\TH:i')) }}" required>

        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
            @foreach ($estados as $valor => $nome)
                <option value="{{ $valor }}" @selected(old('estado', $estadoAtual) === $valor)>{{ $nome }}</option>
            @endforeach
        </select>

        @if ($errors->any())
            <p class="erro">{{ $errors->first() }}</p>
        @endif
        <div class="acoes-formulario">
            <a class="botao botao-secundario" href="{{ url('/gestor/indisponibilidades') }}">Cancelar</a>
            <button class="botao botao-principal" type="submit">Guardar alterações</button>
        </div>
    </form>
</div>
@endsection
