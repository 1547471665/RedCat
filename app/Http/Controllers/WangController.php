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
use App\Models\RangeMoney;
use App\Models\TempReward;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class WangController extends Controller
{

    protected $listen = [
        'App\Events\ExampleEvent' => [
            'App\Listeners\ExampleListener',
        ],
    ];

    public function Index(Request $request)
    {
        return self::GetNumberinterval(10);
//        Redis::set('hello','world');//设置key
//        Redis::expire('hello',10);//设置过期
        return Redis::randomkey();
        $redis = new \Redis();
        $redis->connect('121.40.148.31');
        $redis->auth('Hello_Redis');
        $redis->close();
        echo "Server is running: " . $redis->ping();
//        self::LostLoginTempRewardInsert(date('Y-m-d'));
        die();
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

    private function GetNumberinterval($number)
    {
        $model = RangeMoney::all();
        $model_array = $model->toArray();
        $list = array_unique(array_merge(array_column($model_array, 'min'), array_column($model_array, 'max')));
        sort($list);
        $position = array_search($number, $list);
        if ($position === false) {
            array_push($list, $number);
            sort($list);
            $position = array_search($number, $list);
            $data = [
                'min' => $list[$position - 1],
                'max' => isset($list[$position + 1]) ? $list[$position + 1] : $list[$position - 1],
            ];
        } else {
            if ($position % 2 == 1) {
                $data = [
                    'min' => $list[$position - 1],
                    'max' => $list[$position],
                ];
            } else {
                $data = [
                    'min' => $list[$position],
                    'max' => isset($list[$position + 1]) ? $list[$position + 1] : $list[$position - 1],
                ];
            }

        }
        $result = array_first(array_where($model_array, function ($v) use ($data) {
            return (($v['min'] == $data['min']) && ($v['min'] == $data['min']));
        }));
        $res = rand($result['min_money'] * 100, $result['max_money'] * 100) / 100;
        return $res;
    }


}