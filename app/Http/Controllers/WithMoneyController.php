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

class WithMoneyController extends Controller
{
    private $_user;

    public function __construct(Request $request)
    {
        $this->_user = Auth::user();
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
            $data = 'success';

        } else {
            $data = 'failed';
        }
        return response()->json($data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 获取撒币列表
     */
    public function ListWithMoney(Request $request)
    {
        $max_number = 10;
        $data = RewardUser::where('user_id', $this->_user->id)->get()->toArray();
        $count = count($data);
        if ($count == $max_number) {
            $this->_user->withmoney_status = 0;
            $this->_user->save();
        }
        return response()->json($data);
    }

}