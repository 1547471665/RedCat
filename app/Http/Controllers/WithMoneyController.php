<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/6
 * Time: 20:36
 */

namespace App\Http\Controllers;


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
     * @return \Illuminate\Http\JsonResponse
     * 点击领取撒币
     */
    public function ClickWithMoney(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $max_number = 10;
        $id = $request->input('id');
        $reward_model = RewardUser::find($id);
        $this->_user->money += $reward_model->money;//添加撒币
        $reward_model->delete();//清除奖励池
        if (RewardUser::where('user_id', $this->_user->id)->count() < $max_number) {
        } else {
            $this->_user->withmoney_status = 0;
        }
        if ($this->_user->save()) {
            return response()->json(['StatusCode' => 10000, 'message' => error_code(10000)]);
        } else {
            return response()->json(['StatusCode' => 50000, 'message' => error_code(50000)]);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 获取撒币列表
     */
    public function ListWithMoney(Request $request)
    {
        $max_number = $this->_config['Max_Position'];
        $data = RewardUser::where('user_id', $this->_user->id)->get()->toArray();
        $count = count($data);
        if ($count >= $max_number) {
            $this->_user->withmoney_status = 0;
            $this->_user->save();
        }
        return response()->json(['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $data]);
    }

}