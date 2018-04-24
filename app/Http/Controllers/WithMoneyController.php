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
        if (is_null($this->_user)) {
            abort(40100, error_code(40100));
        }
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
    {
        return RewardUser::ListWithMoney($this->_user);
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