@extends('layouts.app')
@section('titulo', 'Criar indisponibilidade')
@section('conteudo')
<div class="cartao detalhes-reserva">
    <h2>Marcar sala como indisponível</h2>
    <p>Escolhe uma sala, o período e o estado que deve ser aplicado.</p>
    <form method="POST" action="{{ url('/gestor/indisponibilidades') }}">
        @csrf
        <label for="espaco_id">Sala</label>
        <select id="espaco_id" name="espaco_id" required>
            <option value="">Seleciona uma sala</option>
            @foreach ($espacos as $espaco)
                    <option value="{{ $espaco->id }}" @selected((string) old('espaco_id', request('espaco_id')) === (string) $espaco->id)>{{ $espaco->nome_completo }}</option>
            @endforeach
        </select>

        <label for="inicio">Início</label>
        <input id="inicio" name="inicio" type="datetime-local" value="{{ old('inicio') }}" required>

        <label for="fim">Fim</label>
        <input id="fim" name="fim" type="datetime-local" value="{{ old('fim') }}" required>

        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
            <option value="">Seleciona um estado</option>
            @foreach ($estados as $valor => $nome)
                <option value="{{ $valor }}" @selected(old('estado') === $valor)>{{ $nome }}</option>
            @endforeach
        </select>

        @if ($errors->any())
            <p class="erro">{{ $errors->first() }}</p>
        @endif
        <div class="acoes-formulario">
            <a class="botao botao-secundario" href="{{ url('/gestor/indisponibilidades') }}">Cancelar</a>
            <button class="botao botao-principal" type="submit">Guardar indisponibilidade</button>
        </div>
    </form>
</div>
@endsection