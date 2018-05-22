<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/14
 * Time: 22:11
 */

namespace App\Http\Controllers;


use App\Models\RewardHistory;
use Illuminate\Support\Facades\App;
use Yansongda\Pay\Pay;

class WeixinPayController extends Controller
{


    private $_wechat_payment;

    public function __construct()
    {
        $this->_wechat_payment = Pay::wechat(config('pay.wechat'));
    }

    public function Index()
    {
        $order = [
            'out_trade_no' => time(),
            'body' => 'subject-测试',
            'total_fee' => '1',
            'openid' => 'oXP4D5l_XA5ZULmgp1AjyWua4mDQ',
            /*以下是非必传字段*/
            'attach'=>'aabbcc',//附加数据
//            'detail' => '',//商品详情
//            'time_start'=>'',//交易起始时间
//            'time_expire'=>'',//交易结束时间
//            'product_id'=>'',//商品ID
//            'limit_pay'=>'',//指定支付方式
        ];
        $result = $this->_wechat_payment->miniapp($order);
//        $result = $this->_wechat_payment->verify();
        return $result;
    }

    public function Pay()
    {
        $order = [
            'out_trade_no' => time(),
            'body' => 'subject-测试',
            'total_fee' => '1',
            'openid' => 'oXP4D5l_XA5ZULmgp1AjyWua4mDQ',
        ];
        $result = $this->_wechat_payment->miniapp($order);
//        $result = $this->_wechat_payment->verify();
        return $result;

    }

    public function refund()
    {
        $order = [
            'out_trade_no' => '1514192025',
            'out_refund_no' => time(),
            'total_fee' => '1',
            'refund_fee' => '1',
            'refund_desc' => '测试退款haha',
        ];
        $result = $this->_wechat_payment->refund($order);
        return $result;

    }

    public function notify()
    {
        return $this->_wechat_payment->success();

    }

    public function select_order()
    {
        $order = [
            'out_trade_no' => '1514027114',
            'type' => 'miniapp'
        ];

        $result = $this->_wechat_payment->find($order);

        return $result;
    }

    /**
     *
     */
    public function close_order()
    {
        $order = [
            'out_trade_no' => '1514027114',
        ];
        $result = $this->_wechat_payment->close($order);
        return $result;
    }


}