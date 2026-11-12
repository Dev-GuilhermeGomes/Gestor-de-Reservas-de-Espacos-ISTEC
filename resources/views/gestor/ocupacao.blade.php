@extends('layouts.app')
@section('titulo', 'Ocupação global')
@section('conteudo')
<form class="filtros" method="GET">
    <label>Espaço
        <select name="espaco">
            <option value="">Todas</option>
            @foreach ($espacos as $espaco)
                <option value="{{ $espaco->id }}" @selected((string) request('espaco') === (string) $espaco->id)>{{ $espaco->nome_completo }}</option>
            @endforeach
        </select>
    </label>
    <label>Tipo de espaço
        <select name="tipo">
            <option value="">Todos</option>
            @foreach ($tipos as $tipo)
                <option value="{{ $tipo }}" @selected(request('tipo') === $tipo)>{{ $tipo }}</option>
            @endforeach
        </select>
    </label>
    <label>Período
        <select name="periodo" onchange="this.form.submit()">
            <option value="semana" @selected($periodo === 'semana')>Semana atual</option>
            <option value="mes" @selected($periodo === 'mes')>Mês atual</option>
        </select>
    </label>
    <button class="botao botao-principal" type="submit">Aplicar</button>
</form>

<div class="resumo resumo-ocupacao">
    <div class="cartao"><strong>{{ $mediaOcupacao }}%</strong>Taxa média de ocupação</div>
    <div class="cartao"><strong>{{ $totalReservas }}</strong>Reservas aprovadas</div>
    <div class="cartao"><strong>0</strong>Conflitos evitados</div>
</div>

<div class="cartao">
    <h2 class="titulo-cartao">Ocupação por espaço</h2>
    <div class="barras-ocupacao">
        @forelse ($ocupacao as $linha)
            <div class="barra-linha">
                <span>{{ $linha['espaco']->nome }}</span>
                <div class="barra-fundo"><div class="barra-valor" style="width: {{ $linha['percentagem'] }}%"></div></div>
            </div>
        @empty
            <p class="vazio">Não existem espaços disponíveis.</p>
        @endforelse
    </div>
    <p class="nota">Taxa de ocupação calculada com base nas reservas aprovadas para o período selecionado.</p>
</div>
@endsection
