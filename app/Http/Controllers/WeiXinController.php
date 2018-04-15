<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/14
 * Time: 22:11
 */

namespace App\Http\Controllers;


use App\Models\ForceHistory;
use App\Models\TempReward;
use App\Models\User;
use App\weixin\WXBizDataCrypt;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class WeiXinController extends Controller
{
    private $appid;
    private $secret;
    private $access_token;
    private $salt;
    private $_config;


    public function __construct()
    {
        $this->salt = "userloginregister";
        $this->_config = \Illuminate\Support\Facades\Cache::get('setting');
        $this->appid = config('wechat.WECHAT_APPID');
        $this->secret = config('wechat.WECHAT_SECRET');
        self::Token();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     * 微信登陆/注册接口
     */
    public function Login(Request $request)
    {
        $user_info = self::getUserInfo($request);
        $user = User::where('openId', $user_info->openId)->first();
        if ($user) {//用户存在
            $token = str_random(60);
//                $user->api_token = $token;
            $user->login_time = date('Y-m-d');
            $user->save();
            return response()->json(['StatusCode' => 10000, 'message' => error_code(10000), 'data' => ['user_info' => $user]]);
        } else {//用户不存在。注册
            $password = "123456";
            $user = new User;
            $user->username = $user_info->openId;
            $user->password = sha1($this->salt . $password);
            $user->email = 'example@example.com';
            $user->api_token = str_random(60);
            $user->login_time = date('Y-m-d');
            $user->login_status = 1;
            $user->openId = $user_info->openId;
            $user->nickName = $user_info->nickName;
            $user->gender = $user_info->gender;
            $user->language = $user_info->language;
            $user->city = $user_info->city;
            $user->province = $user_info->province;
            $user->country = $user_info->country;
            $user->avatarUrl = $user_info->avatarUrl;
            if (!empty($fid)) {
                $fid = Crypt::decrypt(urldecode($fid));
                $user->invitation_id = $fid;
            }
            if ($user->save()) {
                if (!empty($fid)) {//设置邀请用户奖励
                    self::AcceptInvitation($user, $fid);
                }
                return response()->json(['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $user]);
            } else {
                return abort(50000, error_code(50000));
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 获取用户信息
     */
    public function getUserInfo(Request $request)
    {
        $Open_data = self::Openid($request);
        $sessionKey = $Open_data->session_key;
        $openid = $Open_data->openid;
        $data = $request->input();
        $userData = $request->input('userData');
        $rawData = json_decode($userData);
        $signature = $rawData->signature;
        $encryptedData = $rawData->encryptedData;
        $iv = $rawData->iv;
        $pc = new WXBizDataCrypt($this->appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if ($errCode == 0) {
            return response()->json($data);
        } else {
            return response()->json($errCode);
        }
    }


    /**
     * @param Request $request
     * @return mixed
     * 通过CODE 获取用户的OPENID
     * {"session_key":"thejrwbyEs7Ci1Evgh+HUA==","openid":"oXP4D5k--e9LJmacLAMCKu00_Ais"}
     */
    private function Openid(Request $request)
    {
        $code = $request->input('code');
        $uri = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $this->appid . "&secret=" . $this->secret . "&js_code=" . $code . "&grant_type=authorization_code";
//        return $body_result = json_decode(file_get_contents($uri));
        $client = new Client();
        $res = $client->get($uri);
        $code = $res->getStatusCode(); // 200
        $body = $res->getBody();
        $body_result = json_decode($body->getContents());
        return $body_result;
    }

    /**
     * @return string
     */
    public function Index()
    {
        return ($this->checkSignature()) ? $_GET['echostr'] : 'xx';
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
            $force = $this->_config['Invi_Tmp_Reward_Force']->value;
            $temp_reward_model = new TempReward();
            $temp_reward_model->timestamps = true;
            $temp_reward_model->type = 2;
            $temp_reward_model->user_id = $fid;
            $temp_reward_model->from_id = $user->id;
            $temp_reward_model->start_time = time();
            $temp_reward_model->invalid_time = time() + $this->_config['Tmp_Force_Invalid']->value;
            $temp_reward_model->force = $force;
            $temp_reward_model->save();
        }
        $f_user->save();
        ForceHistory::create([
            'user_id' => $f_user->id,
            'force_value' => $force,
            'type' => 2,
            'from_id' => $user->id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        //添加到队列  通知邀请用户
        return response()->json(['StatusCode' => 10000, 'message' => error_code(10000)]);
    }

    /**
     *获取并设置微信 access_token
     */
    private function Token()
    {
        if (Cache::has('wx_token')) {
            $wx_token = Cache::get('wx_token');
            $this->access_token = $wx_token->access_token;
        } else {
            $uri = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->secret;
//            $wx_token = json_decode(file_get_contents($uri));
            $client = new Client();
            $res = $client->get($uri);
            $code = $res->getStatusCode(); // 200
            $body = $res->getBody();
            $wx_token = json_decode($body->getContents());
            $expiresAt = Carbon::now()->addSeconds($wx_token->expires_in);
            Cache::add('wx_token', $wx_token, $expiresAt);
            $this->access_token = $wx_token->access_token;
        }
    }

    /**
     * @return bool
     * 校验微信消息服务
     */
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = 'redcatclub';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }


}