<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/9
 * Time: 0:45
 */

function error_code($e)
{
    $code = [];
    $code['10000'] = '请求成功';
    $code['10001'] = '签到成功';
    $code['40000'] = '非法请求';
    $code['40100'] = '用户登录失败';
    $code['40101'] = 'token验证失败';
    $code['40102'] = '用户不存在';
    $code['40103'] = '用户已绑定';
    $code['40111'] = '已经签到过了';
    $code['50000'] = '服务器错误';
    $code['50001'] = '文件上传失败';
    return $code[$e];
}

function IndexBy($model, $column)
{
    $list = [];
    foreach ($model as $index => $item) {
        if (is_object($item)) {
            $list[$item->$column] = $item;
        }
        if (is_array($item)) {
            $list[$item[$column]] = $item;
        }
    }
    return $list;
}

function PointTwoStay($num)
{
    return sprintf("%.3f", substr(sprintf("%.6f", $num), 0, -3));
}