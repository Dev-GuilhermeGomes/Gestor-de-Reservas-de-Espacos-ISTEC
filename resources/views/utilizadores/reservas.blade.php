@extends('layouts.app')

@section('titulo', 'As minhas reservas')

@section('conteudo')
    <p>DEBUG sucesso: {{ session('sucesso') ?? 'vazio' }} | erro: {{ session('erro') ?? 'vazio' }}</p>
    
    @if (session('sucesso'))
    <div class="alerta alerta-cancelado" role="status">
        {{ session('sucesso') }}
    </div>
    @endif
    @if (session('erro'))
    <div class="alerta alerta-erro" role="alert">
        {{ session('erro') }}
    </div>
    @endif

    <div class="cartao reservas-cartao">
        <div class="reservas-filtros">
            <nav class="separadores" aria-label="Estado das reservas">
                <span class="ativo" aria-current="page">Aprovados</span>
                <span>Recusados</span>
                <span>Todos</span>
            </nav>

            
        </div>

        <div class="tabela">
            <table>
                <thead>
                    <tr>
                        <th>Espaço</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Finalidade</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservas as $reserva)
                        <tr>
                            <td>{{ $reserva->espaco->nome }}</td>
                            <td>{{ $reserva->inicio->format('d/m/Y') }}</td>
                            <td>
                                {{ $reserva->inicio->format('H:i') }}–{{ $reserva->fim->format('H:i') }}
                            </td>
                            <td>{{ $reserva->motivo }}</td>
                            <td>
                                <span class="estado-reserva estado-reserva--{{ strtolower($reserva->estado) }}">
                                    {{ ucfirst(strtolower($reserva->estado)) }}
                                </span>
                            </td>
                            <td>
                                <div class="acoes-reserva">
                                    <button class="btn btn-reserva" type="button">Alterar</button>
                                    <form action="{{ route('cancelarReserva', $reserva->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-cancelar" type="submit">Cancelar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="vazio" colspan="6">Ainda não tem reservas para apresentar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection