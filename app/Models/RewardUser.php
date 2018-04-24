<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/8
 * Time: 21:29
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RewardUser extends Model
{
    protected $table = 'reward_user';
    protected $fillable = ['withmoneyplan_id', 'money', 'sort', 'number', 'user_id'];

    public static function ListWithMoney($user)
    {//@TODO 固定下标
        $config = Cache::get('setting');
        if (!$config) {
            $config = IndexBy(Setting::all(), 'key');
            Cache::forever('setting', $config);
        }
        $max_number = $config['Max_Position']->value;
        if (is_null($user->reward_position)) {//不存在的话设置默认位置
            $pre_position = $position = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0,];
        } else {
            $pre_position = $position = json_decode($user->reward_position, true);
            $list = self::where('user_id', $user->id)->whereIn('id', $pre_position)->get()->pluck('id')->toArray();
            if (empty($list)) {
                $position = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0,];
            } else {
                foreach ($position as $k => $v) {
                    if (!in_array($v, $list)) {
                        $position[$k] = 0;
                    }
                }
            }
        }
        $data = self::where('user_id', $user->id)->get()->each(function ($model) use (&$position) {
            if (!in_array($model->id, $position, true)) {
                $_position = array_search(0, $position, true);//寻找空位置
                if (false !== $_position) {//没有空位
                    $position[$_position] = $model->id;
                    $model->position = $_position;
                }
            } else {
                $_position = array_search($model->id, $position, true);//记录当前位置
                $model->position = $_position;
            }
        });
        if (!empty(array_diff_assoc($position, $pre_position))) {//两个位置是否一致
            $user->reward_position = json_encode($position);
        }
        $count = count($data);
        if ($count >= $max_number) {
            $user->withmoney_status = 0;
        } else {
            if ($user->withmoney_status != 1) {
                $user->withmoney_status = 1;
            }
        }
        $user->save();
        return [
            'StatusCode' => 10000,
            'message' => error_code(10000),
            'data' => $data,
            'force' => $user->force,
            'money' => $user->money,
            'api_ticket' => $user->id
//            'api_ticket' => Crypt::encrypt($this->_user->id)
        ];
    }
}