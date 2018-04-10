<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/8
 * Time: 14:59
 */

namespace App\Console\Commands;


use App\Models\WithMoneyPlanUser;
use App\User;
use App\WithMoneyPlan;
use Illuminate\Console\Command;
use phpDocumentor\Reflection\Types\Integer;

class TestCommand extends Command
{

    protected $signature = 'test_command';

    protected $description = 'Command Description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        $plan_model = WithMoneyPlan::orderBy('id', 'desc')->first();
//        $model = self::RandomRegister(1001, 1000);//随机注册用户
        $model = self::RandomLogin(200, 88, $plan_model->id);//随机登陆用户
    }

    private function RandomRegister($start, $number)
    {
        $salt = 'userloginregister';
        $_password = sha1($salt . '123456');
        //这里编写要执行的操作
        $datas = [];
        for ($i = $start; $i < $number; $i++) {
            $items = [
                'username' => 'test' . $i,
                'password' => $_password,
                'email' => 'test' . $i . '@example.com',
                'api_token' => str_random(60),
            ];
            array_push($datas, $items);
        }
        $model = User::insert($datas);
    }

    public function RandomLogin($start, $number, $plan_id)
    {
        $user = User::offset($start)->limit($number)->get(['id'])->toArray();
        $list = array_column($user, 'id');
        $datas = [];
        foreach ($list as $user_id) {
            $item = ['withmoneyplan_id' => $plan_id, 'user_id' => $user_id];
            array_push($datas, $item);
        }
        return WithMoneyPlanUser::insert($datas);

    }

}

