<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function alterar(string $id)
    {
        // ver a reserva se for do user
        $reserva = auth()->user()->reservas()->findOrFail($id);


        //quando vai ser possivel cancelar/alterar
        if ($reserva->estado=="CONCLUIDA" || $reserva->estado=="RECUSADA") 
        {
            return back()->with('erro', 'Não é possível alterar uma reserva que esteja concluída ou recusada.');
        }
        
        if ($reserva->data_inicio -> isPast()){
            return back()->with('erro', 'Não é possível alterar uma reserva que já começou.');
        }
        $reserva->update(['data_fim' => $reserva->expira_em->addDays(7)]);
        
        return back()->with('sucesso', 'Reserva alterada.');
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
