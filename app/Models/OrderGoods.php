<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/8
 * Time: 21:29
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class OrderGoods extends Model
{
    protected $table = 'order_goods';
    protected $fillable = ['order_id', 'goods_id', 'price', 'cat_coin', 'params', 'updated_at', 'created_at', 'status', 'remark'];
    public $timestamps = true;


    public function getParamsAttribute($value)
    {
        return json_decode($value);
    }

    public function order_goods_express()
    {
        return $this->hasOne(Express::class,'id','express_id');
    }

}