<?php

namespace App\Http\Middleware;

use Closure;
use \Illuminate\Http\Request;
use \Illuminate\Http\RedirectResponse;

class HttpsProtocol
{

    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return RedirectResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->isSecure() && app()->environment() === 'production') {
            return redirect()->to($request->getRequestUri(), 302, [], true);
        }

        return $next($request);
    }

    /**
     * @return bool
     */
    private function isSecure(): bool
    {
        return (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] === 443);
    }

}