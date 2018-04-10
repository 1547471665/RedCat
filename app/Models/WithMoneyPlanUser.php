<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/8
 * Time: 21:29
 */

namespace App\Models;


use App\User;
use Illuminate\Database\Eloquent\Model;

class WithMoneyPlanUser extends Model
{
    protected $table = 'withmoneyplan_user';
    protected $fillable = ['withmoneyplan_id', 'user_id',];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }


}