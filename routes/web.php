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

$router->get('/', function () use ($router) {
    return "Welcome to visit RedCat ";
});

$router->post('user/login', 'UserController@login');
$router->post('user/register', 'UserController@register');
$router->post('user/register/{fid}', 'UserController@register');
$router->get('user/info', [
    'middleware' => 'authToken',
    'uses' => 'UserController@info'
]);

$router->group(['middleware' => 'authToken', 'prefix' => 'api/v1'], function ($router) {
    $router->get('sblist', 'WithMoneyController@ListWithMoney');
    $router->post('click', 'WithMoneyController@ClickWithMoney');
    $router->get('createinvitation', 'WithMoneyController@CreateInvitationFriendsUrl');
});

$router->get('1', "WangController@Index");

