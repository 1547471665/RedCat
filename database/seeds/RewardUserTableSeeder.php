<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RewardUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $list_reward = \Illuminate\Support\Facades\Cache::get('list_reward');
        $datas = [];
        $plan_model = \App\WithMoneyPlan::all();
        foreach ($plan_model as $number => $model) {
            foreach ($list_reward as $user_id => $float_value) {
                $item = [
                    'withmoneyplan_id' => $model->id,
                    'number' => $number + 1,
                    'money' => $float_value,
                    'sort' => $number + 1,
                    'user_id' => $user_id,
                ];
                array_push($datas,$item);
            }
        }
        DB::table('reward_user')->insert($datas);
    }
}
