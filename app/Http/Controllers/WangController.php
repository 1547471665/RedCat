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
use App\Models\TempReward;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Iwanli\Wxxcx\Wxxcx;

class WangController extends Controller
{

    protected $listen = [
        'App\Events\ExampleEvent' => [
            'App\Listeners\ExampleListener',
        ],
    ];

    public function Index()
    {
        $client = new Client();
        $res = $client->get('https://redcat.daciapp.com');
        echo $res->getStatusCode();
        echo $res->getBody();
        die();

        $params = ['make' => '长城', 'model' => '宏光', 'year' => 2018];
        $date = Carbon::now()->addMinutes(1);
//        $date = Carbon::now()->addMinutes(15);
        Queue::later($date, new WithmoneyJob($params));
//        $this->dispatch(new WithmoneyJob($params));
        die();
        $list_user = [215 => 'aaa', 210 => 'bbb'];
        $temp_reward = TempReward::selectRaw('user_id,SUM(`force`) AS `force`')->where('invalid_time', '>', time())->whereIn('user_id', array_keys($list_user))->groupBy('user_id')->get();
        $list = IndexBy($temp_reward, 'user_id');
        var_dump(array_column($list, 'force'));
        die();
        echo '<pre>';
        print_r(array_sum(array_column($list, 'force')));
        die();
    }

    public function Encrypt()
    {
        $encrypt = Crypt::encrypt('123456');
        $decrypt = Crypt::decrypt($encrypt);
        return response()->json(['encrypt' => $encrypt, 'decrypt' => $decrypt]);
    }

    public function Aaa()
    {
        echo 1;die();
        event(new ExampleListener());
//        echo url('foo/bar', $parameters = [], $secure = null);
//        dd(env('APP_ENV'));
        $result = DB::select('select * from users');
//        dispatch((new WithmoneyJob("这里是队列"))->delay(5));
        return response()->json($result);
    }


}