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
use App\Models\ForceHistory;
use App\Models\TempReward;
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
//        self::LostLoginTempRewardInsert(date('Y-m-d'));
//        die();
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

    /**
     * @param $date
     * @return bool
     * 丢失登陆临时握力的
     */
    private function LostLoginTempRewardInsert($date)
    {
        $user = User::where('login_time', $date)->get()->pluck('nickName', 'id')->toArray();
        $reward = TempReward::where('type', 1)->whereDate('created_at', $date)->whereIn('user_id', array_keys($user))->get()->pluck('created_at', 'user_id')->toArray();
//        $reward = ForceHistory::where('type', 1)->whereDate('created_at', $date)->whereIn('user_id', array_keys($user))->get()->pluck('created_at', 'user_id')->toArray();
        $lost_ids = array_diff(array_keys($user), array_keys($reward));
        foreach ($lost_ids as $k => $v) {
            $temp_reward_model = new TempReward;
            $temp_reward_model->timestamps = true;
            $temp_reward_model->type = 1;
            $temp_reward_model->user_id = $v;
            $temp_reward_model->start_time = time();
            $temp_reward_model->invalid_time = time() + 86400;
            $temp_reward_model->force = 1;
            $temp_reward_model->save();
            ForceHistory::create([
                'user_id' => $v,
                'type' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'force_value' => $temp_reward_model->force,
            ]);
        }
        return true;
    }


}