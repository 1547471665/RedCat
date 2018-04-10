<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/6
 * Time: 21:07
 */

namespace App\Http\Controllers;


use App\Jobs\WithmoneyJob;
use App\Listeners\ExampleListener;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class WangController extends Controller
{

    protected $listen = [
        'App\Events\ExampleEvent' => [
            'App\Listeners\ExampleListener',
        ],
    ];

    public function Encrypt()
    {
        $encrypt = Crypt::encrypt('123456');
        $decrypt = Crypt::decrypt($encrypt);
        return response()->json(['encrypt' => $encrypt, 'decrypt' => $decrypt]);
    }

    public function Aaa()
    {
        event(new ExampleListener());
//        echo url('foo/bar', $parameters = [], $secure = null);
//        dd(env('APP_ENV'));
        $result = DB::select('select * from users');
//        dispatch((new WithmoneyJob("这里是队列"))->delay(5));
        return response()->json($result);
    }


}