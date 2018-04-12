<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/6
 * Time: 20:36
 */

namespace App\Http\Controllers;


use App\Models\TempReward;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    private $salt;
    private $_config;

    public function __construct()
    {
        $this->salt = "userloginregister";
        $this->_config = \Illuminate\Support\Facades\Cache::get('setting');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     * 创建好友邀请链接
     */
    public function CreateInvitationFriendsUrl(Request $request)
    {
        if (empty($this->_user)) {
            abort(40102, error_code(40102));
        } else {
            $url = \url('user/register', ['id' => Crypt::encrypt($this->_user->id)], false);
        }
        return response()->json(['StatusCode' => 10000, 'message' => error_code(10000), 'data' => ['inv_url' => $url]]);
    }


    /**
     * @param Request $request
     * 用户登陆请求
     */
    public function login(Request $request)
    {//如何判断用户在线时长
        if ($request->has('username') && $request->has('password')) {
            $user = User::where('username', '=', $request->input('username'))->where('password', '=', sha1($this->salt . $request->input('password')))->first();
            if ($user) {
                $token = str_random(60);
//                $user->api_token = $token;
                $user->login_time = date('Y-m-d');
                $user->save();
                self::LoginRewardForce($user);
                return response()->json(['StatusCode' => 10000, 'message' => error_code(10000), 'data' => ['api_token' => $user->api_token]]);
            } else {
                return abort(40100, error_code(40100));
            }
        } else {
            return abort(40100, error_code(40100));
        }
    }

    /**
     * @param Request $request
     * @param null $fid 邀请人ID
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \Illuminate\Validation\ValidationException
     * 注册接口
     */
    public function register(Request $request, $fid = null)
    {
        if ($request->has('username') && $request->has('password')) {
            $this->validate($request, [
                'username' => 'required|unique:users'//验证并且是用户表中唯一的
            ]);
            $user = new User;
            $user->username = $request->input('username');
            $user->password = sha1($this->salt . $request->input('password'));
//            $user->email = $request->input('email');
            $user->email = 'example@example.com';
            $user->api_token = str_random(60);
            $user->login_time = date('Y-m-d');
            $user->login_status = 1;
            if (!empty($fid)) {
                $fid = Crypt::decrypt(urldecode($fid));
                $user->invitation_id = $fid;
            }
            if ($user->save()) {
                if (!empty($fid)) {//设置邀请用户奖励
                    self::AcceptInvitation($user, $fid);
                }
                return response()->json(['StatusCode' => 10000, 'message' => error_code(10000)]);
            } else {
                return abort(50000, error_code(50000));
            }
        } else {
            return abort(40000, error_code(40000));
        }
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * 获取用户信息
     */
    public function info()
    {
        return Auth::user();
    }

    /**
     * @param User $user
     * @return bool
     * 登陆获取临时握力
     */
    private function LoginRewardForce(User $user)
    {
        $model = TempReward::where(['type' => 1, 'user_id' => $user->id])->orderBy('id', 'desc')->first();
        if (empty($model) || (date('Y-m-d', $model->start_time) != date('Y-m-d'))) {
            $temp_reward_model = new TempReward();
            $temp_reward_model->timestamps = true;
            $temp_reward_model->type = 1;
            $temp_reward_model->user_id = $user->id;
            $temp_reward_model->start_time = time();
            $temp_reward_model->invalid_time = time() + $this->_config['Tmp_Force_Invalid']->value;
            $temp_reward_model->force = $this->_config['Invi_Tmp_Reward_Force']->value;
            $temp_reward_model->save();
            return true;
        }
        return false;
    }

    /**
     * @param Request $request
     * @param $fid
     * @return \Illuminate\Http\JsonResponse
     * 接受好友邀请
     */
    private function AcceptInvitation(User $user, $fid)
    {
        $f_user = User::find($fid);
        if ($fid == $user->id) {
            abort(40000, error_code(40000));//不能邀请自己
        }
        $number = User::where('invitation_id', $fid)->count();
        if ($number < $this->_config['Invi_Num_Toplimit']->value) {
            $force = $this->_config['Innumber_Reward_Force']->value;
            $f_user->force += $force;
        } else {
            $temp_reward_model = new TempReward();
            $temp_reward_model->timestamps = true;
            $temp_reward_model->type = 2;
            $temp_reward_model->user_id = $fid;
            $temp_reward_model->from_id = $user->id;
            $temp_reward_model->start_time = time();
            $temp_reward_model->invalid_time = time() + $this->_config['Tmp_Force_Invalid']->value;
            $temp_reward_model->force = $this->_config['Invi_Tmp_Reward_Force']->value;
            $temp_reward_model->save();
        }
        $f_user->save();
        //添加到队列  通知邀请用户
        return response()->json(['StatusCode' => 10000, 'message' => error_code(10000)]);
    }
}