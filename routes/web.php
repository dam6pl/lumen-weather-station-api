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
$router->post('/proxy', ['middleware' => ['https', 'cors'], 'uses' => 'ProxyController@resolvePost']);

$router->group(['prefix' => '/v1', 'middleware' => ['https', 'cors']], static function () use ($router) {

    //Users Endpoint
    $router->get('/users[/{id}]', ['uses' => 'UsersController@get']);
    $router->get('/users/{id}/token', ['uses' => 'UsersController@getToken']);
    $router->post('/users[/{id}]', ['uses' => 'UsersController@create']);
    $router->delete('/users/{id}', ['uses' => 'UsersController@delete']);

    //Stations Endpoint
    $router->get('/stations[/{id}]', ['uses' => 'StationsController@get']);
    $router->get('/stations/{id}/measurements', ['uses' => 'StationsController@getMeasurements']);
    $router->post('/stations[/{id}]', ['uses' => 'StationsController@create']);
    $router->delete('/stations/{id}', ['uses' => 'StationsController@delete']);

    //Measurements Endpoint
    $router->get('/measurements[/{id}]', ['uses' => 'MeasurementsController@get']);
    $router->post('/measurements[/{id}]', ['uses' => 'MeasurementsController@create']);
    $router->delete('/measurements/{id}', ['uses' => 'MeasurementsController@delete']);
});