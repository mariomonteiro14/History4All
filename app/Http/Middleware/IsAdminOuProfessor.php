<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class IsAdminOuProfessor
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
        if ($request->user() && ($request->user()->tipo == "admin" || $request->user()->tipo == "professor"))
			return $next($request);
		throw new AccessDeniedHttpException('Unauthorized.');
    }
}
