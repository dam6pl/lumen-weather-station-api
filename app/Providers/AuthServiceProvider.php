<?php

namespace App\Providers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot(): void
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', static function (Request $request) {
            if ($request->input('token')) {
                $request->user = User::where('token', $request->input('token'))->first();
                return $request->user;
            } elseif ($request->header('X-Token-Auth')) {
                $request->user = User::where('token', $request->header('X-Token-Auth'))->first();
                return $request->user;
            }
        });
    }
}
