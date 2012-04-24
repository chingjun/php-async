<?php
abstract class Socket extends EventEmitter {
    protected $fd;
    protected $event_loop;
    final public function __construct() {
        $params = func_get_args();
        call_user_func_array(array($this, 'init'), $params);
    }
    protected function init() {
    }
    public function getFd() {
        return $this->fd;
    }
    public function setEventLoop($ev) {
        $this->event_loop = $ev;
    }
}
