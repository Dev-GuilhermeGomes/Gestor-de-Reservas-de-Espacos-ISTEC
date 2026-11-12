@extends('layouts.app')
@section('titulo', 'Indisponibilidades')
@section('conteudo')
@if (session('sucesso'))
    <p class="sucesso">{{ session('sucesso') }}</p>
@endif

<div class="resumo resumo-indisponibilidades">
    <div class="cartao"><strong>{{ $contagens['DISPONIVEL'] }}</strong>Espaços disponíveis</div>
    <div class="cartao"><strong>{{ $contagens['MANUTENCAO'] }}</strong>Espaços em manutenção</div>
    <div class="cartao"><strong>{{ $contagens['INDISPONIVEL'] }}</strong>Espaços indisponíveis</div>
    <div class="cartao"><strong>{{ $contagens['CANCELADO'] }}</strong>Espaços cancelados</div>
</div>

<div class="cartao">
    <div class="cabecalho-cartao">
        <h2 class="titulo-cartao">Gestão de indisponibilidades</h2>
        <a class="botao botao-principal" href="{{ url('/gestor/indisponibilidades/criar') }}">Nova indisponibilidade</a>
    </div>
    <div class="separadores" role="tablist" aria-label="Estado dos espaços">
        @foreach (['CANCELADO' => 'Cancelados', 'MANUTENCAO' => 'Manutenções', 'INDISPONIVEL' => 'Indisponíveis', 'DISPONIVEL' => 'Disponíveis', 'TODOS' => 'Todos'] as $valor => $nome)
            <a class="{{ $estadoAtual === $valor ? 'ativo' : '' }}" href="{{ url('/gestor/indisponibilidades?estado=' . $valor) }}">{{ $nome }}</a>
        @endforeach
        <button class="botao-pesquisa" type="button" aria-label="Abrir pesquisa" aria-expanded="false" aria-controls="caixa-pesquisa-indisponibilidades">
            <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 5 5"></path></svg>
        </button>
    </div>
    <div class="caixa-pesquisa" id="caixa-pesquisa-indisponibilidades" hidden>
        <label for="pesquisa-indisponibilidades">Pesquisar espaços</label>
        <input id="pesquisa-indisponibilidades" type="search" placeholder="Espaço ou motivo..." autocomplete="off">
    </div>
    <div class="tabela">
        <table>
            <thead><tr><th>Espaço</th><th>Data</th><th>Horário</th><th>Motivo</th><th>Estado</th><th>Ações</th></tr></thead>
            <tbody>
                @forelse ($indisponibilidades as $indisponibilidade)
                    <tr data-indisponibilidade>
                        <td>{{ $indisponibilidade->edificio }} - {{ $indisponibilidade->nome }}</td>
                        <td>{{ \Carbon\Carbon::parse($indisponibilidade->inicio)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($indisponibilidade->inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($indisponibilidade->fim)->format('H:i') }}</td>
                        <td>{{ $indisponibilidade->motivo }}</td>
                        <td><span class="estado estado-{{ strtolower($indisponibilidade->estado) }}">{{ ucfirst(strtolower($indisponibilidade->estado)) }}</span></td>
                        <td>
                            @if ($indisponibilidade->estado !== 'DISPONIVEL')
                                <div class="acoes">
                                    <a class="botao botao-principal" href="{{ url('/gestor/indisponibilidades/' . $indisponibilidade->id . '/editar') }}">Editar</a>
                                    <form method="POST" action="{{ url('/gestor/indisponibilidades/' . $indisponibilidade->id) }}" onsubmit="return confirm('Apagar esta indisponibilidade?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="botao botao-secundario" type="submit">Apagar</button>
                                    </form>
                                </div>
                            @else
                                <a class="botao botao-secundario" href="{{ url('/gestor/indisponibilidades/criar?espaco_id=' . $indisponibilidade->espaco_id) }}">Bloquear</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr data-empty-state><td colspan="6" class="vazio">Não existem indisponibilidades registadas.</td></tr>
                @endforelse
                <tr data-no-results hidden><td colspan="6" class="vazio">Não foram encontrados resultados.</td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>
    const botaoPesquisaIndisponibilidades = document.querySelector('#caixa-pesquisa-indisponibilidades')?.previousElementSibling.querySelector('.botao-pesquisa');
    const caixaPesquisaIndisponibilidades = document.querySelector('#caixa-pesquisa-indisponibilidades');
    const campoPesquisaIndisponibilidades = document.querySelector('#pesquisa-indisponibilidades');
    const linhasIndisponibilidades = [...document.querySelectorAll('[data-indisponibilidade]')];
    const semResultadosIndisponibilidades = document.querySelector('[data-no-results]');

    botaoPesquisaIndisponibilidades?.addEventListener('click', () => {
        caixaPesquisaIndisponibilidades.hidden = !caixaPesquisaIndisponibilidades.hidden;
        botaoPesquisaIndisponibilidades.setAttribute('aria-expanded', String(!caixaPesquisaIndisponibilidades.hidden));
        if (!caixaPesquisaIndisponibilidades.hidden) campoPesquisaIndisponibilidades.focus();
    });

    campoPesquisaIndisponibilidades?.addEventListener('input', () => {
        const pesquisa = campoPesquisaIndisponibilidades.value.trim().toLocaleLowerCase();
        let visiveis = 0;
        linhasIndisponibilidades.forEach((linha) => {
            linha.hidden = !linha.textContent.toLocaleLowerCase().includes(pesquisa);
            if (!linha.hidden) visiveis++;
        });
        if (semResultadosIndisponibilidades) semResultadosIndisponibilidades.hidden = visiveis > 0 || pesquisa === '';
    });
</script>
@endsection
