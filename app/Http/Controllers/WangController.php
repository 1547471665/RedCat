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
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class WangController extends Controller
{

    protected $listen = [
        'App\Events\ExampleEvent' => [
            'App\Listeners\ExampleListener',
        ],
    ];

    public function Index()
    {
        $user = User::first();
        return View('wang.index', ['user' => $user]);
        return "This is WangController";
        $params = ['make' => '长城', 'model' => '宏光', 'year' => 2018];
        $date = Carbon::now()->addMinutes(1);
        $date = Carbon::now()->addMinutes(15);
        Queue::later($date, new WithmoneyJob($params));
        $this->dispatch(new WithmoneyJob($params));
        return url('foo/bar', $parameters = [], $secure = null);
        dd(env('APP_ENV'));
        dispatch((new WithmoneyJob("这里是队列"))->delay(5));
        event(new ExampleListener());
        return DB::select('select * from users');
    }

    public function Encrypt()
    {
        $encrypt = Crypt::encrypt('123456');
        $decrypt = Crypt::decrypt($encrypt);
        return ['encrypt' => $encrypt, 'decrypt' => $decrypt];
    }


}