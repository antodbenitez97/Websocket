<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class JugadorInvitado
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->session()->get('jugador_id');
        if (!$id) {
            $id = (string) Str::uuid();
            $request->session()->put('jugador_id', $id);
        }

        $request->setUserResolver(fn() => new GenericUser(['id' => $id]));

        return $next($request);
    }
}
