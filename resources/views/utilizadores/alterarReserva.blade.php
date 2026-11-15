@extends('layouts.app')

@section('titulo', 'Alterar reserva')

@section('conteudo')
    <a class="alterar-reserva-voltar" href="{{ url('/reservas') }}">← As minhas reservas</a>

    <section class="cartao alterar-reserva-cartao">
        <h2>Dados da reserva</h2>
        <p class="alterar-reserva-descricao">Edite os dados da reserva e guarde as alterações.</p>

        <form class="alterar-reserva-form">
            <label for="data">Data</label>
            <input id="data" type="date" value="{{ $reserva->inicio->format('Y-m-d') }}">

            <label for="hora-inicio">Hora de início</label>
            <input id="hora-inicio" type="time" value="{{ $reserva->inicio->format('H:i') }}">

            <label for="hora-fim">Hora de fim</label>
            <input id="hora-fim" type="time" value="{{ $reserva->fim->format('H:i') }}">

            <label for="espaco">Sala</label>
            <select id="espaco">
                @foreach ($espacos as $espaco)
                    <option value="{{ $espaco->id }}" @selected($espaco->id === $reserva->espaco_id)>
                        {{ $espaco->nome }}
                    </option>
                @endforeach
            </select>

            <label for="motivo">Finalidade da reserva</label>
            <input id="motivo" type="text" value="{{ $reserva->motivo }}">

            <p class="alterar-reserva-aviso">Pode alterar a reserva até 30 minutos antes do início.</p>

            <div class="alterar-reserva-acoes">
                <button class="btn btn-reserva" type="button">Guardar alterações</button>
                <a class="btn btn-cancelar" href="{{ url('/reservas') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection