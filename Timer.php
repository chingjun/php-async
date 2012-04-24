<?php
class Timer extends EventEmitter{
    public $oneshot = false;
    public $interval = 0;
    public $next = 0;
    public $callback = null;
    public function __construct($callback) {
        $this->on('timeout', $callback);
    }
    public function set_timeout($seconds, $oneshot = false) {
        $this->interval = $interval;
        $this->next = gettimeofday(true) + $interval;
    }
    public function timeout() {
        $this->emit('Timeout');
    }
}
