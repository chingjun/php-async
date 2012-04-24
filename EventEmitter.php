<?php
abstract class EventEmitter {
    final public function emit($event) {
        $params = func_get_args();
        array_shift($params);
        $callback = array($this, 'on'.$event);
        if(is_callable($callback)) {
            call_user_func_array($callback, $params);
        }
    }
}
