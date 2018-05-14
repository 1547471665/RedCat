<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/14
 * Time: 22:11
 */

namespace App\Http\Controllers;


use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class WeixinPayController extends Controller
{
    private $appid;
    private $secret;
    private $access_token;
    private $_config;
    private $_uris = [
        'create_order' => 'https://api.mch.weixin.qq.com/pay/unifiedorder',
        'select_order' => 'https://api.mch.weixin.qq.com/pay/orderquery',
        'close_order' => 'https://api.mch.weixin.qq.com/pay/closeorder',
        'refund_order' => 'https://api.mch.weixin.qq.com/secapi/pay/refund',
        'select_refund_order' => 'https://api.mch.weixin.qq.com/pay/refundquery',
        'comment_order' => 'https://api.mch.weixin.qq.com/billcommentsp/batchquerycomment',
    ];


    public function __construct()
    {

        $this->_config = \Illuminate\Support\Facades\Cache::get('setting');
        $this->appid = config('wechat.WECHAT_APPID');
        $this->secret = config('wechat.WECHAT_SECRET');
        self::Token();
    }

    public function Pay()
    {
        $data = [];
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


}