Hello This is Wang PHP blade

<input type="text" name="" id="Msg"/>
<button type="button" onclick="SendMsg()">点击我发送文本消息</button>
<script>
    if ("WebSocket" in window) {
        // alert("您的浏览器支持 WebSocket!");

        // var wsServer = 'ws://47.94.153.58:9502?aaa=bbb';
        var wsServer = 'ws://redcat.daciapp.com:9501/config/list?s=redcat';
        // var wsServer = 'ws://0.0.0.0:9502';
        var websocket = new WebSocket(wsServer);

        websocket.onopen = function (evt) {
            console.log("Connected to WebSocket server.");
        };

        websocket.onclose = function (evt) {
            console.log("Disconnected");
        };

        websocket.onmessage = function (evt) {
            console.log('Retrieved data from server: ' + evt.data);
        };

        websocket.onerror = function (evt, e) {
            console.log('Error occured: ' + evt.data);
        };

        function SendMsg(e) {
            var msg = document.getElementById('Msg');
            console.log(msg.value);
            websocket.send(msg.value)
        }

    }

</script>