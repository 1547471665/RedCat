<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/14
 * Time: 21:52
 */

namespace App\weixin;


/**
 * Prpcrypt class
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class Prpcrypt
{
    public $key;

    public function __construct($k)
    {
        $this->key = base64_decode($k . "=");
    }

//    function Prpcrypt($k)
//    {
//        $this->key = base64_decode($k . "=");
//    }

    /**
     * 对明文进行加密
     * @param string $text 需要加密的明文
     * @return string 加密后的密文
     */
    public function encrypt($text, $appid)
    {
        try {
            //获得16位随机字符串，填充到明文之前
            $random = $this->getRandomStr();
            $text = $random . pack("N", strlen($text)) . $text . $appid;
            // 网络字节序
            var_dump($this->key);
            die();
            $iv = substr($this->key, 0, 16);
            $encrypted = openssl_encrypt($text, 'aes-128-cbc', base64_encode($this->key), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, base64_encode($iv));
            var_dump($encrypted);
            die();
            $encrypt_msg = base64_encode($encrypted);
            //使用自定义的填充方式对明文进行补位填充
//            $pkc_encoder = new PKCS7Encoder;
//            $text = $pkc_encoder->encode($text);
            //使用BASE64对加密后的字符串进行编码
            return array(ErrorCode::$OK, base64_encode($encrypt_msg));
        } catch (Exception $e) {
            //print $e;
            return array(ErrorCode::$EncryptAESError, null);
        }
    }


    /**
     * 对密文进行解密
     * @param string $aesCipher 需要解密的密文
     * @param string $aesIV 解密的初始向量
     * @return string 解密得到的明文
     */
    public function decrypt($aesCipher, $aesIV)
    {
        try {
            //解密
            $decrypted = openssl_decrypt(base64_decode($aesCipher), 'aes-128-cbc', base64_decode($this->key), OPENSSL_RAW_DATA, base64_decode($aesIV));
            // dump(($aesCipher));
            // dump(($this->key));
            // dump(($aesIV));
        } catch (\Exception $e) {
            return false;
        }
        try {
            //去除补位字符
            $pkc_encoder = new PKCS7Encoder;
            $result = $pkc_encoder->decode($decrypted);
        } catch (\Exception $e) {
            //print $e;
            return false;
        }
        return $result;
    }


    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    function getRandomStr()
    {

        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

}