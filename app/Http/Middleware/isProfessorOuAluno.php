<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class isProfessorOuAluno
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user() && ($request->user()->tipo == "aluno" || $request->user()->tipo == "professor"))
            return $next($request);
        throw new AccessDeniedHttpException('Unauthorized.');
    }
}
