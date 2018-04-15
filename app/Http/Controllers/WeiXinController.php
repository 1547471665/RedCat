<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/14
 * Time: 22:11
 */

namespace App\Http\Controllers;


use App\weixin\WXBizDataCrypt;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WeiXinController extends Controller
{
    private $appid;
    private $secret;
    private $access_token;

    public function __construct()
    {
        $this->appid = config('wechat.WECHAT_APPID');
        $this->secret = config('wechat.WECHAT_SECRET');
        self::Token();
    }

    public function Login(Request $request)
    {
        return self::getUserInfo($request);
        /* $post_data = [
             'code' => '0011SueN09FPV42UwBeN0KPyeN01Suev',
             'userData' => '{
     "errMsg": "getUserInfo:ok",
     "rawData": "{\"nickName\":\"少华\",\"gender\":1,\"language\":\"zh_CN\",\"city\":\"Chaoyang\",\"province\":\"Beijing\",\"country\":\"China\",\"avatarUrl\":\"https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTJPem18uLA8O4QabViaT4SwdQhOVjdEoHeNd3mic7L8IqHA1xSSVLzfnbrhGR1vISdWLic7h8dwibFr7g/0\"}",
     "userInfo": {
         "nickName": "少华",
         "gender": 1,
         "language": "zh_CN",
         "city": "Chaoyang",
         "province": "Beijing",
         "country": "China",
         "avatarUrl": "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTJPem18uLA8O4QabViaT4SwdQhOVjdEoHeNd3mic7L8IqHA1xSSVLzfnbrhGR1vISdWLic7h8dwibFr7g/0"
     },
     "signature": "6e2291eb25e734ebd74a2d67320edcc06dad0ca3",
     "encryptedData": "mbzxJO4aZ/YFngt/R0ReZL37BkTMIXYqZAjWI05NQMVMgW4+1J0wM5U4A9VOCxdJztUxzxTAnQb8cqUiq9GsB/ex63K9TVImAHugOl3OUAyBqrXUiPnWNzjRX/jMMnNbUcdzTx9Ozo502G3RNR1Rp3PX9pMXdOYqqjOMe3GgmxsAiauD1/ZDwHR8sUO1u/o8+uPNGI5OBWHlW1VHjpJPHreGK/DcCvhc4zhHX2V6Xx/jBE+5juItimQDfKCP98mDrx7mpfxqUqBBPS4D8J7VvXGGyQpZjNXvh0kK6OSUnhzWMZww5ZGusBFDSkr80KJXuWr24eF/l1tto1xHHBg9yQBNGOYpwhHCiKmgIIrsjfBv4ppvG6HrjRq90oqIjhRKoo0ybMsim/PQxTdc03FwEupSHImu29hchgmJf2Ew/mRh5fdp87skAW22PJVEZYVWlhJoASu2/kHMe9Mihp2Fn+yjtBhW+GlchZxDGEl0+qU=",
     "iv": "YKaR3DU2dEBQ9JRCsZUHAg=="
 }',
         ];
         $client = new Client();
         $response = $client->post('http://shayao.lumen.net/wx/login', ['form_params' => $post_data,]);
         $code = $response->getStatusCode();
         $body = $response->getBody();
         $contents = $body->getContents();*/
    }

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
//        return json_decode('{"session_key":"thejrwbyEs7Ci1Evgh+HUA==","openid":"oXP4D5k--e9LJmacLAMCKu00_Ais"}');
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

    public function Index()
    {
        return ($this->checkSignature()) ? $_GET['echostr'] : 'xx';
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