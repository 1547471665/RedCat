<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/6
 * Time: 20:36
 */

namespace App\Http\Controllers;


use App\Models\RewardHistory;
use App\Models\RewardUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WithMoneyController extends Controller
{
    private $_user;
    private $_config;

    public function __construct(Request $request)
    {
        $this->_user = Auth::user();
        $this->_config = Cache::get('setting');
    }


    /**
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     * 点击领取撒币
     */
    public function ClickWithMoney(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $max_number = 10;
        $id = $request->input('id');
        $reward_model = RewardUser::find($id);
        if (is_null($reward_model) || ($reward_model->user_id != $this->_user->id)) {
            return ['StatusCode' => 40000, 'message' => error_code(40000)];
        }
        $this->_user->money += $reward_model->money;//添加撒币
        if (RewardUser::where('user_id', $this->_user->id)->count() < $max_number) {
        } else {
            $this->_user->withmoney_status = 0;
        }
        if (!is_null($this->_user->reward_position)) {
            $position = json_decode($this->_user->reward_position, true);
            $_position = array_search(intval($id), $position, true);
            if ($_position !== false) {
                $position[$_position] = 0;
                $this->_user->reward_position = json_encode($position);
            }
        }
        if ($this->_user->save()) {
            RewardHistory::create([
                'user_id' => $this->_user->id,
                'money' => $reward_model->money,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                '`type`' => 0,
            ]);
            $reward_model->delete();//清除奖励池
            return ['StatusCode' => 10000, 'message' => error_code(10000)];
        } else {
            return ['StatusCode' => 50000, 'message' => error_code(50000)];
        }
    }


    /**
     * @return array
     * 获取撒币列表
     */
    public function ListWithMoney()
    {//@TODO 固定下标
        $max_number = $this->_config['Max_Position']->value;
        if (is_null($this->_user->reward_position)) {//不存在的话设置默认位置
            $pre_position = $position = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 8 => 0, 9 => 0, 10 => 0,];
        } else {
            $pre_position = $position = json_decode($this->_user->reward_position, true);
            $list = RewardUser::where('user_id', $this->_user->id)->whereIn('id', $pre_position)->get()->pluck('id')->toArray();
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
        $data = RewardUser::where('user_id', $this->_user->id)->get()->each(function ($model) use (&$position) {
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
            $this->_user->reward_position = json_encode($position);
        }
        $count = count($data);
        if ($count >= $max_number) {
            $this->_user->withmoney_status = 0;
        } else {
            if ($this->_user->withmoney_status != 1) {
                $this->_user->withmoney_status = 1;
            }
        }
        $this->_user->save();
        return [
            'StatusCode' => 10000,
            'message' => error_code(10000),
            'data' => $data,
            'force' => $this->_user->force,
            'money' => $this->_user->money,
            'api_ticket' => $this->_user->id
//            'api_ticket' => Crypt::encrypt($this->_user->id)
        ];
    }

    public function MoneyHistory(Request $request)
    {
        $type_name = ["铲币", "签到", "邀请好友"];
        $pageSize = $request->input('pageSize', 10);
        $model = RewardHistory::where('user_id', $this->_user->id)->orderBy('id', 'desc')->paginate($pageSize);
        $data = ['list' => $model, 'money' => $this->_user->money];
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $data];
    }

}