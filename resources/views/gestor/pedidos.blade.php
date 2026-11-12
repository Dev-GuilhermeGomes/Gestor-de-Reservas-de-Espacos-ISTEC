@extends('layouts.app')
@section('titulo', 'Pedidos de reserva')
@section('conteudo')
@if (session('sucesso'))
    <p class="sucesso">{{ session('sucesso') }}</p>
@endif
@if ($errors->any())
    <p class="erro">{{ $errors->first() }}</p>
@endif

<div class="resumo">
    <div class="cartao"><strong>{{ $aprovadasSemana }}</strong>Reservas aprovadas esta semana</div>
    <div class="cartao"><strong>{{ $indisponibilidades }}</strong>Indisponibilidades ativas</div>
</div>

<div class="cartao">
    <div class="separadores" role="tablist" aria-label="Estado dos pedidos">
        <a class="{{ $estadoAtual === 'APROVADA' ? 'ativo' : '' }}" href="{{ url('/gestor?estado=APROVADA') }}">Aprovadas</a>
        <a class="{{ $estadoAtual === 'RECUSADA' ? 'ativo' : '' }}" href="{{ url('/gestor?estado=RECUSADA') }}">Recusadas</a>
        <a class="{{ $estadoAtual === 'TODAS' ? 'ativo' : '' }}" href="{{ url('/gestor?estado=TODAS') }}">Todas</a>
        <button class="botao-pesquisa" type="button" aria-label="Abrir pesquisa" aria-expanded="false" aria-controls="caixa-pesquisa">
            <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 5 5"></path></svg>
        </button>
    </div>
    <div class="caixa-pesquisa" id="caixa-pesquisa" hidden>
        <label for="pesquisa-pedidos">Pesquisar pedidos</label>
        <input id="pesquisa-pedidos" type="search" placeholder="Espaço, requerente ou finalidade..." autocomplete="off">
    </div>
    <div class="tabela">
        <table>
            <thead>
                <tr><th>Espaço</th><th>Requerente</th><th>Data e hora</th><th>Participantes</th><th>Motivo</th><th>Estado</th><th>Ações</th></tr>
            </thead>
            <tbody>
                @forelse ($pedidos as $pedido)
                    <tr data-pedido>
                        <td>{{ $pedido->espaco?->nome_completo ?? $pedido->espaco?->nome ?? 'Espaço desconhecido' }}</td>
                        <td>{{ $pedido->utilizador?->nome ?? 'Utilizador desconhecido' }}</td>
                        <td>{{ $pedido->inicio->format('d/m/Y H:i') }} - {{ $pedido->fim->format('H:i') }}</td>
                        <td>{{ $pedido->participantes }}</td>
                        <td>{{ $pedido->motivo }}</td>
                        <td><span class="estado estado-{{ strtolower($pedido->estado) }}">{{ ucfirst(strtolower($pedido->estado)) }}</span></td>
                        <td>
                            @if ($pedido->estado === 'APROVADA')
                            <div class="acoes">
                                <a class="botao botao-secundario" href="{{ url('/gestor/pedidos/' . $pedido->id . '/recusar') }}">Recusar</a>
                            </div>
                            @elseif ($pedido->estado === 'RECUSADA')
                            <div class="acoes">
                                <a class="botao botao-secundario" href="{{ url('/gestor/pedidos/' . $pedido->id . '/motivo') }}">Ver motivo</a>
                                <a class="botao botao-principal" href="{{ url('/gestor/pedidos/' . $pedido->id . '/editar') }}">Editar</a>
                            </div>
                            @else
                                <span class="acao-concluida">Sem ações disponíveis</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr data-empty-state><td colspan="7" class="vazio">
                        @switch($estadoAtual)
                            @case('APROVADA')
                                Ainda não há pedidos aprovados.
                                @break
                            @case('RECUSADA')
                                Ainda não há pedidos recusados.
                                @break
                            @case('TODAS')
                                Ainda não há pedidos.
                                @break
                            @default
                                Ainda não há reservas aprovadas.
                        @endswitch
                    </td></tr>
                @endforelse
                <tr data-no-results hidden><td colspan="7" class="vazio">Não foram encontrados pedidos para esta pesquisa.</td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>
    const botaoPesquisa = document.querySelector('.botao-pesquisa');
    const caixaPesquisa = document.querySelector('#caixa-pesquisa');
    const campoPesquisa = document.querySelector('#pesquisa-pedidos');
    const pedidos = [...document.querySelectorAll('[data-pedido]')];
    const estadoVazio = document.querySelector('[data-empty-state]');
    const semResultados = document.querySelector('[data-no-results]');

    botaoPesquisa?.addEventListener('click', () => {
        const aberta = !caixaPesquisa.hidden;
        caixaPesquisa.hidden = aberta;
        botaoPesquisa.setAttribute('aria-expanded', String(!aberta));

        if (!aberta) {
            campoPesquisa.focus();
        } else {
            campoPesquisa.value = '';
            pedidos.forEach((pedido) => { pedido.hidden = false; });
            if (estadoVazio) estadoVazio.hidden = false;
            if (semResultados) semResultados.hidden = true;
        }
    });

    campoPesquisa?.addEventListener('input', () => {
        const pesquisa = campoPesquisa.value.trim().toLocaleLowerCase();
        let visiveis = 0;

        if (estadoVazio) estadoVazio.hidden = pesquisa !== '';

        pedidos.forEach((pedido) => {
            const corresponde = pedido.textContent.toLocaleLowerCase().includes(pesquisa);
            pedido.hidden = !corresponde;
            if (corresponde) visiveis++;
        });

        if (semResultados) semResultados.hidden = visiveis > 0 || pesquisa === '';
    });
</script>
@endsection