<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();

        if (! $usuario instanceof Usuario || ! $usuario->isAdmin()) {
            abort(403, 'Acesso não autorizado ao painel administrativo.');
        }

        return $next($request);
    }
}
