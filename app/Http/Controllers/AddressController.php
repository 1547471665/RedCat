<?php
/**
 * Created by PhpStorm.
 * User: wangxinge
 * Date: 18/5/3
 * Time: 下午2:49
 */

namespace App\Http\Controllers;


use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{

    private $_user;

    public function __construct()
    {
        $this->_user = Auth::user();
    }

    public function add(Request $request)
    {
        $model = new Address();
        $model->address = $request->input('address');
        $model->name = $request->input('name');
        $model->phone = $request->input('phone');
        $model->phone = $request->input('phone');
        $model->status = 1;
        $model->user_id = $this->_user->id;
        $model->save();
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $model];

    }

    public function del($id)
    {
        $model = Address::find($id);
        if ($model) {
            $model->delete();
            return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => 'success'];
        } else {
            return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => 'not found'];
        }

    }

    public function set(Request $request, $id)
    {
        $model = Address::find($id);
        $model->address = $request->input('address', $model->address);
        $model->name = $request->input('name', $model->name);
        $model->phone = $request->input('phone', $model->phone);
        $model->status = 1;
        $model->save();
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $model];
    }

    public function index()
    {
        $model = Address::where('user_id', $this->_user->id)->orderBy('id', 'desc')->get();
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $model];
    }

}