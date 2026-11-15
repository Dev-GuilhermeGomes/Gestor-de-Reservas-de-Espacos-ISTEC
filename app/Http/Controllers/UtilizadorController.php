<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Espaco;

class UtilizadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservas = auth()->user()->reservas()->with('espaco')->get();

        return view('utilizadores.reservas', compact('reservas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     */
    public function alterarReserva(string $id)
    {
        // ver a reserva se for do user
        $reserva = auth()->user()->reservas()->findOrFail($id);

        $espacos = Espaco::where('ativo', true)->get(); // so salas que podem ser reservadas

        return view('utilizadores.alterarReserva', compact('reserva', 'espacos'));
    }

    public function cancelarReserva(string $id)
    {
        // ver a reserva se for do user
        $reserva = auth()->user()->reservas()->findOrFail($id);

        //so 'e cancelavel se :
        $estadosPermitidos = ['PENDENTE', 'APROVADA'];

        if(!in_array($reserva->estado, $estadosPermitidos, true))
        {
            return back()->with('erro', 'Não é possível cancelar uma reserva que não esteja pendente ou aprovada.');       

        }

        if ($reserva->inicio->lt(now()->addMinute(30))) {
            return back()->with('erro', 'Não é possível cancelar uma reserva que vá começar em menos de 30 minutos.');
        }
        $reserva->estado = 'CANCELADA';
        $reserva->save();
        return back()->with('sucesso', 'Reserva cancelada.');
    }

} 
