<?php
class TcpServer extends Socket {
    private $timeout;
    protected $factory = 'TCPSocket';
    public function init($factory) {
        $this->factory = $factory;
    }
    public function listen($port, $interface = '0.0.0.0') {
        $this->fd = stream_socket_server($interface.':'.$port);
        stream_set_blocking($this->fd, 0);
        //TODO check fd error
        if (!is_resource($this->fd)) {
            //call error
        }
        $this->event_loop->registerSocket($this, EventLoop::EV_READ);
    }
    public function setTimeout($time) {
        $this->timeout = $time;
    }
    public function onSocketRead() {
        $this->emit('Accept');
    }
    public function onAccept() {
        $client_fd = stream_socket_accept($this->fd);
        stream_set_blocking($client_fd, 0);
        $client = new $this->factory();
        $client->fd = $client_fd;
        $this->event_loop->registerSocket($client, EventLoop::EV_READ);
        $client->emit('Connected', $client);
    }
}
