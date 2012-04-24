<?php
class TcpServer extends Socket {
    //private $read_callback;
    private $timeout;
    //private $callbacks = array();
    protected $factory = 'TCPSocket';
    public function init($factory) {
        $this->factory = $factory;
        //$this->read_callback = $callback;
        //if(!empty($callback)) {
        //    $this->callbacks['read'] = $callback;
        //}
    }
    //public function set_callback($event, $callback) {
    //    $this->callbacks[$event] = $callback;
    //}
    public function listen($port, $interface = '0.0.0.0') {
        $this->fd = stream_socket_server($interface.':'.$port);
        stream_set_blocking($this->fd, 0);
        //TODO check fd error
        if (!is_resource($this->fd)) {
            //call error
        }
        //$this->on('socket_read', array($this, 'on_accept'));
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
        //$client->on('read', $this->read_callback);
        //foreach($this->callbacks as $k=>$v) {
        //    $client->setCallback($k, $v);
        //}
        $this->event_loop->registerSocket($client, EventLoop::EV_READ);
        $client->emit('Connected', $client);
    }
}
