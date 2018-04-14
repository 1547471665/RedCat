<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/14
 * Time: 22:11
 */

namespace App\Http\Controllers;


class WeiXinController extends Controller
{

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

    public function Index()
    {
        return ($this->checkSignature()) ? $_GET['echostr'] : 'xx';
    }
}