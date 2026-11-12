<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function mostrar(): View
    {
        return view('auth.login');
    }

    public function entrar(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // só entram utilizadores ativos
        if (Auth::attempt([...$dados, 'ativo' => true])) {
            $request->session()->regenerate();
            $user = Auth::user();
            $request->session()->put('two_factor.pending_user_id', $user->getAuthIdentifier());
            Auth::logout();

            if ($user->two_factor_secret === null) {
                $request->session()->put('two_factor.setup_secret', app(Google2FA::class)->generateSecretKey());

                return redirect('/two-factor/setup');
            }

            return redirect('/two-factor/challenge');
        }

        return back()->withErrors(['email' => 'E-mail ou palavra-passe incorretos.'])->onlyInput('email');
    }

    public function mostrarConfiguracao(Request $request): View|RedirectResponse
    {
        $user = $this->utilizadorPendente($request);

        if ($user === null) {
            return redirect('/login');
        }

        $secret = $request->session()->get('two_factor.setup_secret');
        if (! is_string($secret)) {
            return redirect('/two-factor/challenge');
        }

        $uri = app(Google2FA::class)->getQRCodeUrl('Reservas ISTEC', $user->email, $secret);

        return view('auth.two-factor-setup', compact('secret', 'uri'));
    }

    public function configurar(Request $request): RedirectResponse
    {
        $user = $this->utilizadorPendente($request);
        $secret = $request->session()->get('two_factor.setup_secret');

        if ($user === null || ! is_string($secret)) {
            return redirect('/login');
        }

        $dados = $request->validate(['code' => ['required', 'digits:6']]);
        if (! $this->codigoValido($secret, $dados['code'])) {
            return back()->withErrors(['code' => 'O código de autenticação é inválido ou expirou.']);
        }

        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        return $this->concluirAutenticacao($request, $user);
    }

    public function mostrarDesafio(Request $request): View|RedirectResponse
    {
        if ($this->utilizadorPendente($request) === null) {
            return redirect('/login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verificar(Request $request): RedirectResponse
    {
        $user = $this->utilizadorPendente($request);

        if ($user === null || ! is_string($user->two_factor_secret)) {
            return redirect('/login');
        }

        $dados = $request->validate(['code' => ['required', 'digits:6']]);
        if (! $this->codigoValido($user->two_factor_secret, $dados['code'])) {
            return back()->withErrors(['code' => 'O código de autenticação é inválido ou expirou.']);
        }

        return $this->concluirAutenticacao($request, $user);
    }

    public function sair(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function utilizadorPendente(Request $request): ?User
    {
        $id = $request->session()->get('two_factor.pending_user_id');

        return is_int($id) || is_string($id) ? User::query()->where('ativo', true)->find($id) : null;
    }

    private function codigoValido(string $secret, string $code): bool
    {
        $google2fa = app(Google2FA::class);
        $timestamp = $google2fa->getTimestamp();

        return (bool) $google2fa->findValidOTP($secret, $code, 1, $timestamp - 1, $timestamp);
    }

    private function concluirAutenticacao(Request $request, User $user): RedirectResponse
    {
        Auth::login($user);
        $request->session()->forget(['two_factor.pending_user_id', 'two_factor.setup_secret']);
        $request->session()->regenerate();

        return redirect()->intended('/painel');
    }
}
