<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/13
 * Time: 10:25
 */

return [
    /**
     * 小程序APPID
     */
    'appid' => 'wx385b551a1fc1f1e1',
    /**
     * 小程序Secret
     */
    'secret' => 'a4a8d600f9ba1fff621e5a2e6685185d',
    /**
     * 小程序登录凭证 code 获取 session_key 和 openid 地址，不需要改动
     */
    'code2session_url' => "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",
];
