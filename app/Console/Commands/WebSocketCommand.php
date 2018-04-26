<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/18
 * Time: 0:23
 */

namespace App\Console\Commands;


use App\Models\RewardUser;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class WebSocketCommand extends Command
{
    protected $signature = 'redcat:websocket';

    protected $description = '开启WebSocket服务';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        //创建websocket服务器对象，监听0.0.0.0:9502端口
        $ws = new \swoole_websocket_server("0.0.0.0", 9502);
        if (App::environment() != 'local') {
            $ws->set([
//            'task_worker_num' => 5,
                'daemonize' => true,//守护进程 长时间运行 要开启
                'ssl_cert_file' => '/etc/nginx/cert/214587431540625.pem',
                'ssl_key_file' => '/etc/nginx/cert/214587431540625.key',
                'backlog' => 128,]);
        }

        //监听WebSocket连接打开事件
        $ws->on('open', function ($ws, $request) {
//            var_dump($request->fd, $request->get, $request->server);
            $ws->tick(60000, function () use ($ws, $request) {//执行循环任务
                if (isset($request->get['api_token']) && $request->server['path_info'] == "/wss/sblist") {
                    $user = User::where('api_token', $request->get['api_token'])->first();
                    if (!is_null($user)) {
                        $list = RewardUser::ListWithMoney($user);
                        $ws->push($request->fd, json_encode($list));
                    }
                }
//                $ws->push($request->fd, "定时······\n");
            });
            if (isset($request->get['api_token']) && $request->server['path_info'] == "/wss/sblist") {
                $user = User::where('api_token', $request->get['api_token'])->first();
                if (!is_null($user)) {
                    $list = RewardUser::ListWithMoney($user);
                    $ws->push($request->fd, json_encode($list));
                }
            }
        });

//        $ws->on('task',function ($data) {
//            echo 111;
//        });
//
//        $ws->on('finish', function ($data) {
//            echo 222;
//        });


        //监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
            echo "Message:{$frame->fd}····· {$frame->data}\n";
            $ws->push($frame->fd, "server: {$frame->data}");
        });

        $ws->on('request', function ($request, $response) {
//            var_dump($request)
            $response->end("<h1>hello swoole</h1>");
        });

        //监听WebSocket连接关闭事件
        $ws->on('close', function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
        });

        $ws->start();

    }

}