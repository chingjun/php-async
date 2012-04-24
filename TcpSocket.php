<?php
class TcpSocket extends Socket {
    protected $writebuffer;
    protected $is_server = false;
    public function init() {
        parent::init();
        $this->factory = get_class($this);
    }

    //client
    public function write($data) {
        if(feof($this->fd)) {
            return;
        }
        if (empty($this->writebuffer))
            $this->event_loop->registerSocket($this, EventLoop::EV_READ | EventLoop::EV_WRITE);
        $this->writebuffer .= $data;
        $this->emit('SocketWrite');
    }
    public function onSocketWrite() {
        $writecount = fwrite($this->fd, $this->writebuffer);
        $this->writebuffer = substr($this->writebuffer, $writecount);
        if (empty($this->writebuffer)) {
            $this->event_loop->registerSocket($this, EventLoop::EV_READ);
            $this->emit('WriteBufferEmpty');
        }
    }
    public function onSocketRead() {
        if($this->is_server) {
            $this->emit('Accept');
            return;
        }
        if(feof($this->fd)) {
            $this->close();
            return;
        }
        $data = fread($this->fd, 10240);
        $this->emit('Read', $data);
    }
    public function close() {
        $this->event_loop->removeSocket($this);
        fclose($this->fd);
    }


    //server
    public function listen($port, $interface = '0.0.0.0') {
        $this->is_server = true;
        $this->fd = stream_socket_server($interface.":".$port);
        stream_set_blocking($this->fd, 0);

        //TODO check fd error
        if (!is_resource($this->fd)) {
            //call error
            var_dump("is not resource!!!");
        }

        $this->event_loop->registerSocket($this, EventLoop::EV_READ);
    }
    public function onAccept() {
        $client_fd = stream_socket_accept($this->fd);
        stream_set_blocking($client_fd, 0);
        $client = new $this->factory();
        $client->fd = $client_fd;
        $this->event_loop->registerSocket($client, EventLoop::EV_READ);
        $client->emit('Connected');
    }
}
