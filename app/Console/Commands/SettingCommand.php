<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/8
 * Time: 14:59
 */

namespace App\Console\Commands;


use App\Models\Setting;
use App\Models\WithMoneyPlanUser;
use App\User;
use App\WithMoneyPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use phpDocumentor\Reflection\Types\Integer;

class SettingCommand extends Command
{

    protected $signature = 'redcat:set_config';

    protected $description = '将设置信息存储到缓存中';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Cache::forever('setting', IndexBy(Setting::all(), 'key'));
    }


}

