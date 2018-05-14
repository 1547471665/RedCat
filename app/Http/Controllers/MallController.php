<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/6
 * Time: 21:07
 */

namespace App\Http\Controllers;


use App\Models\Mall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MallController extends Controller
{
    private $_user;

    protected $listen = [
        'App\Events\ExampleEvent' => [
            'App\Listeners\ExampleListener',
        ],
    ];

    public function __construct()
    {
        $this->_user = Auth::user();
    }

    public function Index()
    {
        $data = [];
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $data];
    }

    /**
     * 兑换商品列表
     * @param Request $request
     * @return array
     */
    public function List(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $data = Mall::where('status', 1)->orderBy('id', 'desc')->paginate($pageSize);
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $data];
    }

    /**
     * 购物车列表
     * @param Request $request
     * @return array
     */
    public function Shopcar(Request $request)
    {
        $cache = Cache::store('file');
        if ($cache->has('shopcar_' . $this->_user->id)) {
            $data = $cache->get('shopcar_' . $this->_user->id);
        } else {
            $data = [];
        }
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $data];
    }

    /**
     * 编辑购物车
     * @param Request $request
     * @return array
     */
    public function ShopCarSet(Request $request, $id)
    {
        $goods = Mall::find($id);
        if (!$goods) {
            abort(40200, error_code(40200));
        }
//        foreach ($request->input() as $key => $value) {
//            if ($key != 'api_token') {
//                $goods->$key = $value;
//            }
//        }
        $goods->num = $request->input('num', 1);
        $goods->params = $request->except(['api_token', 'goods_id', 'num']);
        $cache = Cache::store('file');
        if ($cache->has('shopcar_' . $this->_user->id)) {
            $data = $cache->get('shopcar_' . $this->_user->id);
            $data[$id] = $goods;
        } else {
            $data[$id] = $goods;
        }
        $cache->forever('shopcar_' . $this->_user->id, $data);
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $data];
    }

    /**
     * 清空购物车
     * @param Request $request
     * @return array
     */
    public function Clear(Request $request)
    {
        $cache = Cache::store('file');
        if ($cache->has('shopcar_' . $this->_user->id)) {
            $data = $cache->forget('shopcar_' . $this->_user->id);
        }
        return ['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $data];
    }


}