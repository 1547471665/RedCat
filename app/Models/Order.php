<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/8
 * Time: 21:29
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'order';
    protected $fillable = [];
    public $timestamps = true;

    public function order_goods()
    {
        return $this->hasMany(OrderGoods::class, 'order_id', 'id');
    }

    public function order_address()
    {
        return $this->hasOne(Address::class, 'id', 'address_id');
    }

}