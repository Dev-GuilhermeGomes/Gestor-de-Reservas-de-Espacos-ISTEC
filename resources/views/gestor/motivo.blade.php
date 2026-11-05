@extends('layouts.app')
@section('titulo', 'Motivo da recusa')
@section('conteudo')
<div class="cartao detalhes-reserva">
    <h2>Motivo da recusa</h2>
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
    </dl>
    <div class="motivo-destaque">{{ $pedido->justificacao_decisao ?? 'Sem motivo indicado.' }}</div>
    <div class="acoes-formulario">
        <a class="botao botao-secundario" href="{{ url('/gestor?estado=RECUSADA') }}">Voltar às recusadas</a>
    </div>
</div>
@endsection
