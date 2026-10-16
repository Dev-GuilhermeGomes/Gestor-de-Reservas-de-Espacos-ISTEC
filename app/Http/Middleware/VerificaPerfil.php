<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificaPerfil
{
    public function handle(Request $request, Closure $next, string ...$perfis): Response
    {
        if (! in_array($request->user()->perfil, $perfis, true)) {
            abort(403, 'Não tens permissão para aceder a esta área.');
        }
        return $next($request);
    }
}