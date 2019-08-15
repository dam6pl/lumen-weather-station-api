<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizePath
{
    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $sanitized = \str_replace('\/', '/', $request->getRequestUri());

        if ($sanitized !== $request->getRequestUri()) {
            return \redirect()->to($sanitized);
        }

        return $next($request);
    }
}