<?php

namespace App\Console;

use App\Console\Commands\SettingCommand;
use App\Console\Commands\TestCommand;
use App\Console\Commands\WithmoneyCommand;
use App\Models\Car;
use Illuminate\Console\Scheduling\Schedule;
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
        $params = ['make' => 'xixixi', 'model' => '222', 'year' => 2018];
        $schedule->call(function () use ($params) {
            Car::create($params);
        })->everyMinute();

    }
}
