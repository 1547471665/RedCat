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

//$router->post('user/login', 'UserController@login');
//$router->post('user/register', 'UserController@register');
//$router->post('user/register/{fid}', 'UserController@register');

$router->get('/', function () use ($router) {
    return "Welcome to visit RedCat ";
});
$router->get('user/info', [
    'middleware' => 'authToken',
    'uses' => 'UserController@info',
]);//获取用户信息  √
$router->post('user/login', 'WeiXinController@Login');//登陆/注册 √
$router->group(['middleware' => 'authToken', 'prefix' => 'api/v1'], function ($router) {
    $router->get('sblist', 'WithMoneyController@ListWithMoney');//猫币列表
    $router->post('click', 'WithMoneyController@ClickWithMoney');//点击领取猫币
    $router->get('money', 'WithMoneyController@MoneyHistory');//猫币记录
    $router->get('createinvitation', 'UserController@CreateInvitationFriendsUrl');//创建邀请链接
    $router->get('sign', 'UserController@SignRewardForce');//签到 √
    $router->get('force', 'UserController@ForceList');//握力记录
    $router->get('getshare', 'WeiXinController@AcceptInvitation');//接受邀请
    $router->post('getshare', 'WeiXinController@AcceptInvitation');//接受邀请
    $router->get('sendmsg', 'WeiXinController@SendMsgByCustomService');//发送客服消息


    $router->get('address', 'AddressController@index');//收货地址列表
    $router->post('address', 'AddressController@add');//添加收货地址
    $router->post('address/{id}', 'AddressController@set');//修改收货地址
    $router->delete('address/{id}', 'AddressController@del');//删除收货地址

    $router->get('mall', 'MallController@List');//商城列表
    $router->get('shopcar', 'MallController@Shopcar');//购物车
    $router->post('shopcar/{id}', 'MallController@ShopCarSet');//修改购物车
    $router->delete('shopcar', 'MallController@Clear');//清空购物车

    $router->get('order', 'OrderController@Index');//获取订单信息
    $router->post('order', 'OrderController@Add');//创建订单
    $router->post('order/{id}', 'OrderController@Complete');//完成订单
    $router->delete('order', 'OrderController@Del');//删除订单
    $router->post('pay', 'WeixinPayController@Pay');//支付订单

});
$router->group(['prefix' => 'wx'], function ($router) {
    $router->get('/', "WeiXinController@Index");//消息服务器配置验证 √
});
$router->group(['prefix' => 'config'], function ($router) {
    $router->get('list', "SettingController@index");//配置列表
    $router->get('add', "SettingController@create");//配置新增
    $router->get('set/{id}', "SettingController@update");//配置设置
    $router->get('del', "SettingController@delete");//配置删除
});
$router->get('socket', 'WangController@Index');
$router->get('payment', 'WeixinPayController@Index');

