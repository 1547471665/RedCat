<?php
/**
 * Created by PhpStorm.
 * User: wangxinge
 * Date: 18/5/3
 * Time: 下午2:35
 */

namespace App\Http\Controllers;


use App\Models\MallParams;
use App\Models\Order;
use App\Models\OrderGoods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private $_user;

    public function __construct()
    {
        $this->_user = Auth::user();
    }

    public function Add(Request $request)
    {
        $cache = Cache::store('file');
        if ($cache->has('shopcar_' . $this->_user->id)) {
            $data = $cache->get('shopcar_' . $this->_user->id);
        } else {
            $data = $request->input('order_goods');
        }
        DB::beginTransaction();
        try {
            $model = new Order();
            $model->user_id = $this->_user->id;
            $model->address_id = $request->input('address_id');
            $model->order_id = self::getOrderNumber();
            $model->remark = $request->input('remark');//备注
            $model->status = 1;
            $model->save();
            $model_data = [];
            foreach ($data as $index => $item) {
                $params_name = MallParams::whereIn('key', array_keys($item->params))->get()->pluck('name', 'key')->toArray();
                $_data = [];
                $_data['order_id'] = $model->id;
                $_data['goods_id'] = $item->id;
                $_data['price'] = $item->price;
                $_data['cat_coin'] = $item->money;
                $_data['status'] = 1;
                $_data['updated_at'] = date('Y-m-d H:i:s');
                $_data['created_at'] = date('Y-m-d H:i:s');
                $_data['params'] = json_encode(array_combine($params_name, $item->params));
                array_push($model_data, $_data);
            }
            OrderGoods::insert($model_data);
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(50000, error_code(50000));
        }
        DB::commit();
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => 'success'];

    }

    public function Del($id)
    {
        $model = Order::find($id);
        $model->status = 0;
        $model->save();
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => 'success'];

    }

    public function OrderSet(Request $request, $id)
    {
        $model = Order::find($id);
        $data = $model;
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $data];
    }

    public function Index()
    {
        $model = Order::with(['order_address', 'order_goods.order_goods_express'])->where('user_id', $this->_user->id)->orderBy('id', 'desc')->get();
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $model];
    }

    private function getOrderNumber()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }

}