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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/encrypt', 'WangController@encrypt');

$router->post('user/login', 'UserController@login');
$router->post('user/register', 'UserController@register');
$router->get('user/info', [
    'middleware' => 'authToken',
    'uses' => 'UserController@info'
]);

$router->group(['middleware' => 'authToken', 'prefix' => 'api/v1'], function ($router) {
    $router->get('sblist', 'WithMoneyController@ListWithMoney');
    $router->post('click', 'WithMoneyController@ClickWithMoney');
//    $router->put('car/{id}', 'WithMoneyController@updateCar');
//    $router->delete('car/{id}', 'WithMoneyController@deleteCar');
});

$router->group(['prefix' => '1'], function ($router) {
    $router->get('aaa', 'WangController@aaa');
    $router->get('bbb', function (Request $request) {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users'//验证email 必填，格式为email 并且是用户表中唯一的
        ]);
    });
});

/*$router->group(['prefix' => 'api/v1'], function ($router) {
    $router->post('car', 'CarController@createCar');
    $router->put('car/{id}', 'CarController@updateCar');
    $router->delete('car/{id}', 'CarController@deleteCar');
    $router->get('car', 'CarController@index');
});*/
