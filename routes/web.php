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

$router->get('user/info', [
    'middleware' => 'authToken',
    'uses' => 'UserController@info',
]);//获取用户信息  √
$router->post('user/login', 'WeiXinController@Login');//登陆/注册 √
//$router->post('user/login', 'UserController@login');
//$router->post('user/register', 'UserController@register');
//$router->post('user/register/{fid}', 'UserController@register');
$router->group(['middleware' => 'authToken', 'prefix' => 'api/v1'], function ($router) {
    $router->get('sblist', 'WithMoneyController@ListWithMoney');//猫币列表
    $router->post('click', 'WithMoneyController@ClickWithMoney');//点击领取猫币
    $router->get('createinvitation', 'WithMoneyController@CreateInvitationFriendsUrl');//创建邀请链接
    $router->get('money', 'WithMoneyController@MoneyHistory');//猫币记录
    $router->get('sign', 'UserController@SignRewardForce');//签到 √
    $router->get('force', 'UserController@ForceList');//握力记录
    $router->get('mall', 'MallController@List');//商城列表
    $router->get('getshare', 'WeiXinController@AcceptInvitation');//接受邀请
    $router->post('getshare', 'WeiXinController@AcceptInvitation');//接受邀请
    $router->get('sendmsg', 'WeiXinController@SendMsgByCustomService');//接受邀请
});
$router->group(['prefix' => 'wx'], function ($router) {
    $router->get('/', "WeiXinController@Index");//消息服务器配置验证 √
});

