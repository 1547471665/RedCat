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
use App\Models\TempReward;
use App\Models\WithMoneyPlan;
use App\Models\WithMoneyPlanUser;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WithmoneyCommand extends Command
{

    protected $signature = 'withmoney';

    protected $description = 'With Money';

    private $_config;

    public function __construct()
    {
        $this->_config = Cache::get('setting');
        parent::__construct();
    }

    public function handle()
    {
        self::ClearExpireWithPlan();//清除过期撒币计划
        if ($model = WithMoneyPlan::orderBy('id', 'desc')->first()) {//获取最后一条撒币记录
//            self::CarveUp($this->_config, $model);//分币
            self::CarveUp2($this->_config, $model);//分币
            self::AddPlan($this->_config, $model->id);
        } else {
            self::AddPlan($this->_config, 0);
        }
//        php D:\phpStudy\PHPTutorial\WWW\lumen\artisan test_command
    }

    private function CarveUp2($config, WithMoneyPlan $model)
    {
        if (RewardUser::where('withmoneyplan_id', $model->id)->count() === 0 && ($model->dispense_invalid_time > time())) {//判断是否已经分配过了，或者已经过期
            $max_number = $config['set_number']->value;
            $all_force = 0;
            $except_user = [];
            $list_user = [];
            $datas = [];
            $user_model = User::where(['login_time' => date('Y-m-d')])->get();//获取登陆时间为当天的用户
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
            //获取有效用户 并且 临时握力还未失效的握力 根据用户ID分组
            $temp_reward = TempReward::selectRaw('user_id,SUM(`force`) AS `force`')->where('invalid_time', '>', time())->whereIn('user_id', array_keys($list_user))->groupBy('user_id')->get();
            $temp_forces = IndexBy($temp_reward, 'user_id');
            $all_tmp_forces = array_sum(array_column($temp_forces, 'force'));
            $every_point_number = $model->number / ($all_force + $all_tmp_forces);
            foreach ($user_model as $user) {
                if (array_key_exists($user->id, $list_user)) {
                    $get_money_value = isset($temp_forces[$user->id]) ? (($user->force + $temp_forces[$user->id]->force) * $every_point_number) : ($user->force * $every_point_number);
//                $datas[$item->user_id] = round($get_money_value, 3);
                    $item = [
                        'withmoneyplan_id' => $model->id,
                        'user_id' => $user->id,
                        'money' => round($get_money_value, 3),
                        'sort' => $list_user[$user->id]['num'] + 1,
                        'number' => $list_user[$user->id]['num'] + 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'udpated_at' => date('Y-m-d H:i:s'),
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
            $temp_reward = TempReward::selectRaw('user_id,SUM(`force`) AS `force`')->where('invalid_time', '>', time())->whereIn('user_id', array_keys($list_user))->groupBy('user_id')->get();
            $temp_forces = IndexBy($temp_reward, 'user_id');
            $all_tmp_forces = array_sum(array_column($temp_forces, 'force'));
            $every_point_number = $model->number / ($all_force + $all_tmp_forces);
            foreach ($relation_model as $item) {
                if (array_key_exists($item->user_id, $list_user)) {
                    $get_money_value = isset($temp_forces[$user->id]) ? (($item->user->force + $temp_forces[$item->user->id]->force) * $every_point_number) : ($item->user->force * $every_point_number);
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

