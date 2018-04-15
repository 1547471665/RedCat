<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/6
 * Time: 21:07
 */

namespace App\Http\Controllers;


use App\Models\Mall;
use Illuminate\Support\Facades\Request;

class MallController extends Controller
{

    protected $listen = [
        'App\Events\ExampleEvent' => [
            'App\Listeners\ExampleListener',
        ],
    ];

    public function Index()
    {
        $data = [];
        return response()->json(['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $data]);
    }

    public function List(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $data = Mall::where('status', 1)->orderBy('id', 'desc')->paginate($pageSize)->toArray();
        return response()->json(['StatusCode' => 10000, 'message' => error_code(10000), 'data' => $data]);
    }


}