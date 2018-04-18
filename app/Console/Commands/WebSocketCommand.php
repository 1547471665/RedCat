<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/18
 * Time: 0:23
 */

namespace App\Console\Commands;


use Illuminate\Console\Command;

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

        //监听WebSocket连接打开事件
        $ws->on('open', function ($ws, $request) {
            var_dump($request->fd, $request->get, $request->server);
            $ws->push($request->fd, "hello, welcome\n");
        });

        //监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
            echo "Message: {$frame->data}\n";
            $ws->push($frame->fd, "server: {$frame->data}");
        });

        //监听WebSocket连接关闭事件
        $ws->on('close', function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
        });

        $ws->start();
    }

}