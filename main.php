<?php
function __autoload($class) {
    if(file_exists($class.'.php')) {
        require_once($class.'.php');
    }
}

class EchoSocket extends TcpSocket {
    public function onConnected() {
        echo "Connected\n";
        var_dump(stream_socket_get_name($this->fd, true));
    }
    public function onRead($data) {
        $this->write($data);
    }
}
class MyHttpSocket extends HttpSocket {
    public function onRequestDone($headers, $body) {
        var_dump($headers);
        var_dump($body);
        $ret = "Asynchronous PHP HTTP Server\r\n";
        $this->write("HTTP/1.1 200 OK\r\nContent-Length:".strlen($ret)."\r\n\r\n$ret\r\n");
    }
}

$loop = new EventLoop();
$echoserver = new TcpServer('EchoSocket');
$loop->registerSocket($echoserver);
$echoserver->listen(8988);
$httpserver = new HttpServer('MyHttpSocket');
$loop->registerSocket($httpserver);
$httpserver->listen(8080);
$loop->run();
