<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function mostrar()
    {
        return view('auth.login');
    }

    public function entrar(Request $request)
    {
        $dados = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // só entram utilizadores ativos
        if (Auth::attempt([...$dados, 'ativo' => true])) {
            $request->session()->regenerate();
            return redirect()->intended('/painel');
        }

        return back()->withErrors(['email' => 'E-mail ou palavra-passe incorretos.'])->onlyInput('email');
    }

    public function sair(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}