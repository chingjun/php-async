<?php
class EventLoop {
    private $timer_list = array();
    private $socket_read_list = array();
    private $socket_write_list = array();
    private $socket_except_list = array();

    private $socket_list = array();

    private static $auto_increment_id = 0;
    
    const EV_READ = 1;
    const EV_WRITE = 2;
    const EV_EXCEPT = 4;
    const EV_ALL = 7;

    public static function now() {
        return gettimeofday(true);
    }

    public function registerTimer($timer) {
        $this->timer_list[] = $timer;
    }
    public function removeSocket($socket) {
        unset($this->socket_list[$socket->ev_loop_id]);
    }
    public function registerSocket($socket, $flag = self::EV_ALL) {
        if (!isset($socket->ev_loop_id)) $socket->ev_loop_id = self::$auto_increment_id++;
        if (!is_integer($flag)) {
            return false;
        }
        if ($flag == 0) {
            $this->removeSocket($socket);
            return true;
        }
        $this->socket_list[$socket->ev_loop_id] = array($socket, $flag);
        $socket->setEventLoop($this);
    }
    private function getClosestTimer() {
        $closest = 0x7fffffffffffffff;
        $ctimer = null;
        foreach($this->timer_list as $t) {
            if ($t->next < $closest) {
                $closest = $t->next;
                $ctimer = $timer;
            }
        }
        return array($closest, $ctimer);
    }
    private function getSelectList() {
        $readlist = array();
        $writelist = array();
        $exceptlist = array();
        $this->fd_list = array();
        foreach($this->socket_list as $s) {
            $fd = $s[0]->getFd();
            if($s[1] & self::EV_READ)
               $readlist[] = $fd;
            if($s[1] & self::EV_WRITE)
                $writelist[] = $fd;
            if($s[1] & self::EV_EXCEPT)
                $exceptlist[] = $fd;
            $this->fd_list[$fd] = $s[0];
        }
        return array($readlist, $writelist, $exceptlist);
    }
    public function select($timeout) {
        $arrlist = $this->getSelectList();
        stream_select($arrlist[0], $arrlist[1], $arrlist[2], (int)$timeout, (int)(($timeout - (int)$timeout)*1000000));
        $result = array('read'=>array(), 'write'=>array(), 'except'=>array());
        foreach($arrlist[0] as $fd) {
            $result['read'][] = $this->fd_list[$fd];
        }
        foreach($arrlist[1] as $fd) {
            $result['write'][] = $this->fd_list[$fd];
        }
        foreach($arrlist[2] as $fd) {
            $result['except'][] = $this->fd_list[$fd];
        }
        return $result;
    }
    public function run() {
        while(true) {
            $closest_timer = $this->getClosestTimer();
            $timeout = $closest_timer[0] - self::now();
            $sockets = $this->select($timeout>0?$timeout:0);
            if (self::now() >= $closest_timer[0]) {
                $closest_timer[1]->timeout();
            }
            foreach($sockets['read'] as $socket) {
                echo "1\n";
                $socket->emit('SocketRead');
            }
            foreach($sockets['write'] as $socket) {
                echo "2\n";
                $socket->emit('SocketWrite');
            }
            foreach($sockets['except'] as $socket) {
                echo "3\n";
                $socket->emit('SocketExcept');
            }
        }
    }
}
