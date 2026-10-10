<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GuardaIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! in_array(Auth::user()->perfil, ['operador', 'supervisor'], true)) {
            abort(403, 'Acesso não autorizado ao módulo da portaria.');
        }

        return $next($request);
    }
}
