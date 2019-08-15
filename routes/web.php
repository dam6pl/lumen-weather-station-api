<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/** @var $router Laravel\Lumen\Routing\Router */
$router->group(['prefix' => '/v1', 'middleware' => ['sanitizePath', 'https', 'cors']], static function () use ($router) {

    //Users Endpoint
    $router->get('/users[/{id}]', ['middleware' => 'authAdministrator', 'uses' => 'UsersController@get']);
    $router->get('/users/{id}/token', ['middleware' => 'authAdministrator', 'uses' => 'UsersController@getToken']);
    $router->post('/users[/{id}]', ['middleware' => 'authAdministrator', 'uses' => 'UsersController@create']);
    $router->delete('/users/{id}', ['middleware' => 'authAdministrator', 'uses' => 'UsersController@delete']);

    //Stations Endpoint
    $router->get('/stations[/{id}]', ['uses' => 'StationsController@get']);
    $router->get('/stations/{id}/measurements', ['uses' => 'StationsController@getMeasurements']);
    $router->post('/stations[/{id}]', ['middleware' => 'authAdministrator', 'uses' => 'StationsController@create']);
    $router->delete('/stations/{id}', ['middleware' => 'authAdministrator', 'uses' => 'StationsController@delete']);

    //Measurements Endpoint
    $router->get('/measurements[/{id}]', ['uses' => 'MeasurementsController@get']);
    $router->post('/measurements[/{id}]', ['middleware' => 'auth', 'uses' => 'MeasurementsController@create']);
    $router->delete('/measurements/{id}', ['middleware' => 'authAdministrator', 'uses' => 'MeasurementsController@delete']);
});
