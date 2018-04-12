<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/8
 * Time: 21:29
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TempReward extends Model
{
    protected $table = 'temp_reward';
    protected $fillable = ['type', 'user_id', 'from_id', 'force', 'start_time', 'invalid_time'];
    public $timestamps = false;


}