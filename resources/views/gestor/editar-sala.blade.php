@extends('layouts.app')
@section('titulo', 'Editar sala')
@section('conteudo')
@if (session('sucesso'))
    <p class="sucesso">{{ session('sucesso') }}</p>
@endif
<div class="cartao detalhes-reserva">
    <h2>Editar sala</h2>
    <form method="POST" action="{{ url('/gestor/salas/' . $espaco->id) }}">
        @csrf
        @method('PUT')
        <div class="form-grelha">
            <div>
                <label for="edificio">Edifício</label>
                <input id="edificio" name="edificio" value="{{ old('edificio', $espaco->edificio) }}" required maxlength="100">
            </div>
            <div>
                <label for="nome">Nome da sala</label>
                <input id="nome" name="nome" value="{{ old('nome', $espaco->nome) }}" required maxlength="100">
            </div>
            <div>
                <label for="tipo">Tipo de espaço</label>
                <select id="tipo" name="tipo" required>
                    <option value="SALA" @selected(old('tipo', $espaco->tipo) === 'SALA')>Sala</option>
                    <option value="LABORATORIO" @selected(old('tipo', $espaco->tipo) === 'LABORATORIO')>Laboratório</option>
                    <option value="AUDITORIO" @selected(old('tipo', $espaco->tipo) === 'AUDITORIO')>Auditório</option>
                    <option value="ESPACO_ESTUDO" @selected(old('tipo', $espaco->tipo) === 'ESPACO_ESTUDO')>Espaço de estudo</option>
                </select>
            </div>
            <div>
                <label for="capacidade">Capacidade</label>
                <input id="capacidade" name="capacidade" type="number" min="1" value="{{ old('capacidade', $espaco->capacidade) }}" required>
            </div>
            <div>
                <label for="piso">Piso</label>
                <input id="piso" name="piso" type="number" value="{{ old('piso', $espaco->piso) }}">
            </div>
        </div>
        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao">{{ old('descricao', $espaco->descricao) }}</textarea>
        @if ($errors->any())
            <p class="erro">{{ $errors->first() }}</p>
        @endif
        <div class="acoes-formulario">
            <a class="botao botao-secundario" href="{{ url('/gestor/salas') }}">Cancelar</a>
            <button class="botao botao-principal" type="submit">Guardar alterações</button>
        </div>
    </form>
</div>

<div class="cartao lista-salas">
    <h2 class="titulo-cartao">Equipamentos da sala</h2>
    <form method="POST" action="{{ url('/gestor/salas/' . $espaco->id . '/equipamentos') }}">
        @csrf
        <div class="form-grelha">
            <div>
                <label for="equipamento_nome">Nome do equipamento</label>
                <input id="equipamento_nome" name="nome" required maxlength="100" placeholder="Projetor">
            </div>
            <div>
                <label for="equipamento_descricao">Descrição</label>
                <input id="equipamento_descricao" name="descricao" maxlength="255" placeholder="Projetor de vídeo">
            </div>
        </div>
        <label class="opcao-checkbox">
            <input type="checkbox" name="reservavel" value="1">
            Pode ser reservado separadamente
        </label>
        <div class="acoes-formulario">
            <button class="botao botao-principal" type="submit">Adicionar equipamento</button>
        </div>
    </form>

    <div class="tabela">
        <table>
            <thead><tr><th>Equipamento</th><th>Descrição</th><th>Reservável</th></tr></thead>
            <tbody>
                @forelse ($espaco->equipamentos as $equipamento)
                    <tr>
                        <td>{{ $equipamento->nome }}</td>
                        <td>{{ $equipamento->descricao ?: '-' }}</td>
                        <td>{{ $equipamento->reservavel ? 'Sim' : 'Não' }}</td>
                    </tr>
                @empty
                    <tr><td class="vazio" colspan="3">Ainda não existem equipamentos associados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
