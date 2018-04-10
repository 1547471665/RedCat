<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/8
 * Time: 14:59
 */

namespace App\Console\Commands;


use App\Models\RewardUser;
use App\Models\Setting;
use App\Models\WithMoneyPlanUser;
use App\User;
use App\WithMoneyPlan;
use Illuminate\Console\Command;

class WithmoneyCommand extends Command
{

    protected $signature = 'withmoney';

    protected $description = 'With Money';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
//        self::SetConfig('set_time_dispense_limit', 15 * 60, '发币失效时间间隔');
//        die();
        $model_setting = Setting::whereIn('key', [
            'set_time_money_number',
            'set_time_invalid_limit',
            'set_number',
            'set_time_dispense_limit'
        ])->get(['key', 'value']);
        $config = IndexBy($model_setting, 'key');
        self::ClearExpireWithPlan();//清楚过期撒币计划
        if ($model = WithMoneyPlan::orderBy('id', 'desc')->first()) {
//            self::CarveUp($config, $model);//分币
            self::CarveUp2($config, $model);//分币
            self::AddPlan($config, $model->id);
        } else {
            self::AddPlan($config, 0);
        }
//        php D:\phpStudy\PHPTutorial\WWW\lumen\artisan test_command
    }

    private function CarveUp2($config, WithMoneyPlan $model)
    {
        if (RewardUser::where('withmoneyplan_id', $model->id)->count() === 0 && ($model->dispense_invalid_time > time())) {
            $max_number = $config['set_number']->value;
            $all_force = 0;
            $except_user = [];
            $list_user = [];
            $datas = [];
            $user_model = User::where(['login_time' => date('Y-m-d')])->get();
            foreach ($user_model as $user) {
                if ($user->rewarduser) {
                    $num = $user->rewarduser->count();
                    if ($num < $max_number) {
                        $all_force += $user->force;
                        $list_user[$user->id] = ['user_id' => $user->id, 'num' => $num];
                    } else {
                        array_push($except_user, $user->id);
                    }
                } else {
                    $all_force += $user->force;
                    $list_user[$user->id] = ['user_id' => $user->id, 'num' => 0];
                }
            }
            $every_point_number = $model->number / $all_force;
            foreach ($user_model as $user) {
                if (array_key_exists($user->id, $list_user)) {
                    $get_money_value = $user->force * $every_point_number;
//                $datas[$item->user_id] = round($get_money_value, 3);
                    $item = [
                        'withmoneyplan_id' => $model->id,
                        'user_id' => $user->id,
                        'money' => round($get_money_value, 3),
                        'sort' => $list_user[$user->id]['num'] + 1,
                        'number' => $list_user[$user->id]['num'] + 1,
                    ];
                    array_push($datas, $item);
                }
            }//存储，等待用户领取
            RewardUser::insert($datas);
        }
    }

    /**
     * @param WithMoneyPlan $model
     * 设置用户应得奖励
     */
    private function CarveUp($config, WithMoneyPlan $model)
    {
        if (RewardUser::where('withmoneyplan_id', $model->id)->count() === 0 && ($model->dispense_invalid_time > time())) {
            $max_number = $config['set_number']->value;
            $all_force = 0;
            $except_user = [];
            $list_user = [];
            $datas = [];
            $relation_model = WithMoneyPlanUser::where(['withmoneyplan_id' => $model->id])->get();
            foreach ($relation_model as $item) {
                if ($item->user->rewarduser) {
                    $num = $item->user->rewarduser->count();
                    if ($num < $max_number) {
                        $all_force += $item->user->force;
                        $list_user[$item->user_id] = ['user_id' => $item->user_id, 'num' => $num];
                    } else {
                        array_push($except_user, $item->user_id);
                    }
                } else {
                    $all_force += $item->user->force;
                    $list_user[$item->user_id] = ['user_id' => $item->user_id, 'num' => 0];
                }
            }
            $every_point_number = $model->number / $all_force;
            foreach ($relation_model as $item) {
                if (array_key_exists($item->user_id, $list_user)) {
                    $get_money_value = $item->user->force * $every_point_number;
//                $datas[$item->user_id] = round($get_money_value, 3);
                    $item = [
                        'withmoneyplan_id' => $model->id,
                        'user_id' => $item->user_id,
                        'money' => round($get_money_value, 3),
                        'sort' => $list_user[$item->user_id]['num'] + 1,
                        'number' => $list_user[$item->user_id]['num'] + 1,
                    ];
                    array_push($datas, $item);
                }
            }//存储，等待用户领取
            RewardUser::insert($datas);
        }
//        Cache::add('list_reward', $datas, '20');
    }

    /**
     * @param $last_id
     * 添加撒币计划
     */
    private function AddPlan($config, $last_id)
    {
        $invalid_time = $config['set_time_invalid_limit']->value;
        $dispense_invalid_time = $config['set_time_dispense_limit']->value;
        $data = [
            'number' => $config['set_time_money_number']->value,
            'last_id' => $last_id,
            'start_time' => time(),
            'invalid_time' => time() + $invalid_time,
            'dispense_invalid_time' => time() + $dispense_invalid_time,
        ];
        WithMoneyPlan::create($data);
    }


    /**
     *清除过期撒币计划
     */
    private function ClearExpireWithPlan()
    {
        $expire_plan_model = WithMoneyPlan::where('invalid_time', '<', time())->get();
        if ($expire_plan_model) {
            $model = IndexBy($expire_plan_model, 'id');
            WithMoneyPlan::where('invalid_time', '<', time())->update(['status' => 0]);
//            WithMoneyPlanUser::whereIn('withmoneyplan_id', array_keys($model))->delete();
            RewardUser::whereIn('withmoneyplan_id', array_keys($model))->delete();
        }
    }

    /**
     *设置配置参数
     */
    private function SetConfig(string $key, string $value, string $des)
    {
        $data = [
            'key' => $key,
            'value' => $value,
            'des' => $des,
        ];
        Setting::create($data);
    }


}

