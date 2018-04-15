<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/8
 * Time: 21:29
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ForceHistory extends Model
{
    protected $table = 'force_history';
    protected $fillable = ['user_id', 'force_value', 'created_at', 'updated_at', '`type`', 'from_id'];
}