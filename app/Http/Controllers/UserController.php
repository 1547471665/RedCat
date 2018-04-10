<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/6
 * Time: 20:36
 */

namespace App\Http\Controllers;


use App\Events\Event;
use App\Events\UserEvent;
use App\Listeners\UserListener;
use App\User;
use App\WithMoneyPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private $salt;

    public function __construct()
    {
        $this->salt = "userloginregister";
    }

//登录
    public function login(Request $request)
    {//如何判断用户在线时长
        if ($request->has('username') && $request->has('password')) {
            $user = User::where('username', '=', $request->input('username'))->where('password', '=', sha1($this->salt . $request->input('password')))->first();
            if ($user) {
                $token = str_random(60);
                $user->api_token = $token;
                $user->login_time = date('Y-m-d');
                $user->save();
                return $user->api_token;
            } else {
                return '用户名或密码不正确,登录失败';
            }
        } else {
            return '登录信息不完整,请输入用户名和密码';
        }
    }

//注册
    public function register(Request $request)
    {
        if ($request->has('username') && $request->has('password')) {
            $this->validate($request, [
                'username' => 'required|unique:users'//验证email 必填，格式为email 并且是用户表中唯一的
            ]);
            $user = new User;
            $user->username = $request->input('username');
            $user->password = sha1($this->salt . $request->input('password'));
//            $user->email = $request->input('email');
            $user->email = 'example@example.com';
            $user->api_token = str_random(60);
            if ($user->save()) {
                return '用户注册成功!';
            } else {
                return '用户注册失败!';
            }
        } else {
            return '请输入完整用户信息!';
        }
    }

//信息
    public function info()
    {
        return Auth::user();
    }
}