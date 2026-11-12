@extends('layouts.app')
@section('titulo', 'Criar sala')
@section('conteudo')
<div class="cartao detalhes-reserva">
    <h2>Criar sala</h2>
    <p>Depois de criar a sala, podes gerir os equipamentos na opção <strong>Editar</strong>.</p>
    <form method="POST" action="{{ url('/gestor/salas') }}">
        @csrf
        <div class="form-grelha">
            <div>
                <label for="edificio">Edifício</label>
                <input id="edificio" name="edificio" value="{{ old('edificio', 'ISTEC') }}" required maxlength="100">
            </div>
            <div>
                <label for="nome">Nome da sala</label>
                <input id="nome" name="nome" value="{{ old('nome') }}" required maxlength="100">
            </div>
            <div>
                <label for="tipo">Tipo de espaço</label>
                <select id="tipo" name="tipo" required>
                    <option value="">Seleciona um tipo</option>
                    <option value="SALA" @selected(old('tipo') === 'SALA')>Sala</option>
                    <option value="LABORATORIO" @selected(old('tipo') === 'LABORATORIO')>Laboratório</option>
                    <option value="AUDITORIO" @selected(old('tipo') === 'AUDITORIO')>Auditório</option>
                    <option value="ESPACO_ESTUDO" @selected(old('tipo') === 'ESPACO_ESTUDO')>Espaço de estudo</option>
                </select>
            </div>
            <div>
                <label for="capacidade">Capacidade</label>
                <input id="capacidade" name="capacidade" type="number" min="1" value="{{ old('capacidade', 1) }}" required>
            </div>
            <div>
                <label for="piso">Piso</label>
                <input id="piso" name="piso" type="number" value="{{ old('piso', 0) }}">
            </div>
        </div>
        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao">{{ old('descricao') }}</textarea>
        @if ($errors->any())
            <p class="erro">{{ $errors->first() }}</p>
        @endif
        <div class="acoes-formulario">
            <a class="botao botao-secundario" href="{{ url('/gestor/salas') }}">Cancelar</a>
            <button class="botao botao-principal" type="submit">Criar sala</button>
        </div>
    </form>
</div>
@endsection