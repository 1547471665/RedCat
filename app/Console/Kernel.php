<?php

namespace App\Console;

use App\Console\Commands\SettingCommand;
use App\Console\Commands\TestCommand;
use App\Console\Commands\WithmoneyCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        TestCommand::class,
        WithmoneyCommand::class,
        SettingCommand::class,
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
        if (Cache::has('setting')) {
            $config = Cache::get('setting');
            $schedule->command('withmoney')->cron($config['Cron_Plan_With_Money']->value);
        } else {
            $schedule->command('withmoney')->everyFifteenMinutes();
        }
    }
}
