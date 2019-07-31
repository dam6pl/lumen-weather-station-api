<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;

class AuthenticateAdministrator
{
    /**
     * The authentication guard factory instance.
     *
     * @var /Illuminate/Contracts/Auth/Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param Auth $auth
     *
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request     $request
     * @param Closure     $next
     * @param string|null $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            return response()->json(
                [
                    'success' => false,
                    'error' => [
                        'message' => "Unauthorized request URL ({$request->method()}: {$request->path()}).",
                        'code'    => 'unauthorized_request_error'
                    ]
                ],
                401
            );
        }

        if ($request->user()->role !== 'administrator') {
            return response()->json(
                [
                    'success' => false,
                    'error' => [
                        'message' => "Forbidden request URL ({$request->method()}: {$request->path()}).",
                        'code'    => 'forbidden_request_error'
                    ]
                ],
                403
            );
        }

        return $next($request);
    }
}
