<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/8
 * Time: 21:29
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class RewardUser extends Model
{
    protected $table = 'reward_user';
    protected $fillable = ['withmoneyplan_id', 'money', 'sort', 'number', 'user_id'];
}