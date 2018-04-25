<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/14
 * Time: 22:11
 */

namespace App\Http\Controllers;


use App\Models\ForceHistory;
use App\Models\RewardHistory;
use App\Models\TempReward;
use App\Models\User;
use App\weixin\WXBizDataCrypt;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * @param $user
     * 文本内容....<a href="http://www.qq.com" data-miniprogram-appid="appid" data-miniprogram-path="pages/index/index">点击跳小程序</a>
     */
    public function SendMsgByCustomService(Request $request)
    {
        if ($request->has(['openid', 'msg'])) {
            $openid = $request->input('openid');
            $msg = $request->input('msg', 'Hello World');
        } else {
            return abort(40000, error_code(40000));
        }
        $uri = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->access_token;
        $client = new Client();
        $res = $client->post($uri, [
            'form_params' => [
                'touser' => $openid,
                'msgtype' => 'text',
                'text' => [
                    'content' => $msg,
                ],
            ]
        ]);
        $body = $res->getBody();
        return $body->getContents();
    }

    /**
     *  邀请ID  tGDHvTOQlX8oNKzI_yV0AsGnTxzIED2h0FRfCW1_9Ag
     * 用户昵称{{keyword1.DATA}}  温馨提示 {{keyword2.DATA}}  备注{{keyword3.DATA}}
     */
    public function SendMsgTemplate($user, $template_id = "")
    {
        $template_id = "tGDHvTOQlX8oNKzI_yV0AsGnTxzIED2h0FRfCW1_9Ag";
        $uri = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $this->access_token;
        $client = new Client();
        $res = $client->post($uri, [
            'form_params' => [
                'touser' => $user->openId,
                'template_id' => $template_id,
//                'page' => 'page',
                'form_id' => 'FORMID',
                'data' => [
                    'keyword1' => [
                        "value" => $user->nickName,
                        "color" => "#004D40",
                    ],
                    'keyword2' => [
                        "value" => "一首凉凉送给你",
                        "color" => "#173177",
                    ],
                    'keyword3' => [
                        "value" => "积分已经到账",
                        "color" => "#173177",
                    ],
                ],
                'color' => '#FF1744',
                'emphasis_keyword' => 'keyword1.DATA',
            ]
        ]);
        $body = $res->getBody();
        return $body->getContents();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     * 微信登陆/注册接口
     */
    public function Login(Request $request)
    {
        if ($request->has('api_token')) {
            $user = User::where('api_token', $request->input('api_token'))->first();
            if (is_null($user)) {
                abort(40100, error_code(40100));
            }
            if (self::continuation_days($user)) {
                $user->continuation_days = $user->continuation_days + 1;
            } else {
                $user->continuation_days = 1;
            }
            $token = str_random(60);
            $user->api_token = $token;
            $user->login_time = date('Y-m-d');
            $user->save();
            self::TempRewardForce($user);//添加登陆临时握力
            unset($user->username);
            unset($user->openid);
            return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => ['user_info' => $user]];
        }
        $user_info = json_decode(self::getUserInfo($request));
        $user = User::where('openId', $user_info->openId)->first();
        if ($user) {//用户存在
            $token = str_random(60);
            $user->api_token = $token;
            if (self::continuation_days($user)) {
                $user->continuation_days = $user->continuation_days + 1;
            } else {
                $user->continuation_days = 1;
            }
            $user->login_time = date('Y-m-d');
            $user->save();
            unset($user->username);
            unset($user->openid);
            self::TempRewardForce($user);//添加登陆临时握力
            return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => ['user_info' => $user]];
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
            $user->force = $this->_config['Reg_Reward_Force']->value;
            if ($user->save()) {
                unset($user->username);
                unset($user->openid);
                return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => ['user_info' => $user]];
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
            return $data;
        } else {
            return $errCode;
        }
    }

    private function continuation_days($user)
    {
        $Yesterday = date("Y-m-d", strtotime("-1 day"));
        if ($user->login_time == $Yesterday) {
            return true;
        } else {
            return false;
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

    private function TempRewardForce($user)
    {
        $model = TempReward::where(['type' => 1, 'user_id' => $user->id])->orderBy('id', 'desc')->first();
        if (empty($model) || (date('Y-m-d', $model->start_time) != date('Y-m-d'))) {
            $temp_reward_model = new TempReward();
            $temp_reward_model->timestamps = true;
            $temp_reward_model->type = 1;
            $temp_reward_model->user_id = $user->id;
            $temp_reward_model->start_time = time();
            $temp_reward_model->invalid_time = time() + $this->_config['Tmp_Force_Invalid']->value;
            $temp_reward_model->force = $this->_config['Login_Tmp_Reward_Force']->value;
            $temp_reward_model->save();
            ForceHistory::create([
                'user_id' => $user->id,
                'force_value' => $temp_reward_model->force,
                'type' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Request $request
     * @param $fid
     * @return \Illuminate\Http\JsonResponse
     * 接受好友邀请
     */
    public function AcceptInvitation(Request $request)
    {
        if ($request->has(['api_token', 'api_ticket'])) {
            $user = Auth::user();
            if ($user->invitation_id > 0) {
                return abort(40000, '该用户已经被邀请过');
            }
            $api_ticket = $request->input('api_ticket');
//            $fid = Crypt::decrypt(urldecode($api_ticket));
            $fid = $api_ticket;
            $user->invitation_id = $fid;
            $f_user = User::find($fid);
            if (is_null($f_user)) {
                return abort(40000, '邀请人不存在');
            }
        } else {
            return abort(40000, error_code(40000));
        }
        if ($fid == $user->id || $fid > $user->id) {
            return ['StatusCode' => 10000, 'message' => error_code(10000)];
//            abort(40000, error_code(40000));//不能邀请自己
        }
        $user->save();
        $number = User::where('invitation_id', $fid)->count();
        if ($number < $this->_config['Invi_Num_Toplimit']->value) {
            $force_type = 1;
            $force = $this->_config['Innumber_Reward_Force']->value;
            $f_user->force += $force;
        } else {
            $force_type = 2;
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
        $f_user->money = $f_user->money + 1;
        $f_user->save();
        ForceHistory::create([
            'user_id' => $f_user->id,
            'force_value' => $force,
            'type' => 2,
            'from_id' => $user->id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        RewardHistory::create([
            'user_id' => $f_user->id,
            'money' => 1,
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'type' => 2,
            'from_id' => $user->id,
        ]);
        //添加到队列  通知邀请用户
        $data = [
            'money' => 1,
            'force' => ($force_type == 1) ? $force : 0,
            'tmp_force' => ($force_type == 2) ? $force : 0,
        ];
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $data];
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