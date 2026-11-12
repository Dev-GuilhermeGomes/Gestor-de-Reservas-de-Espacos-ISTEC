@extends('layouts.app')
@section('titulo', 'Recusar reserva')
@section('conteudo')
<div class="cartao detalhes-reserva">
    <h2>Recusar reserva</h2>
    <p>Indica o motivo pelo qual esta reserva não pode ser mantida.</p>

    <dl>
        <div>
            <dt>Espaço</dt>
            <dd>{{ $pedido->espaco?->nome_completo ?? $pedido->espaco?->nome ?? 'Espaço desconhecido' }}</dd>
        </div>
        <div>
            <dt>Requerente</dt>
            <dd>{{ $pedido->utilizador?->nome ?? 'Utilizador desconhecido' }}</dd>
        </div>
        <div>
            <dt>Data e horário</dt>
            <dd>{{ $pedido->inicio->format('d/m/Y H:i') }} - {{ $pedido->fim->format('H:i') }}</dd>
        </div>
        <div>
            <dt>Finalidade</dt>
            <dd>{{ $pedido->motivo }}</dd>
        </div>
    </dl>

    <form method="POST" action="{{ url('/gestor/pedidos/' . $pedido->id . '/recusar') }}">
        @csrf
        <label for="justificacao_decisao">Motivo da recusa</label>
        <textarea id="justificacao_decisao" name="justificacao_decisao" required minlength="3">{{ old('justificacao_decisao') }}</textarea>
        @error('justificacao_decisao')
            <p class="erro">{{ $message }}</p>
        @enderror
        <div class="acoes-formulario">
            <a class="botao botao-secundario" href="{{ url('/gestor') }}">Cancelar</a>
            <button class="botao botao-principal" type="submit">Confirmar recusa</button>
        </div>
    </form>
</div>
@endsection
