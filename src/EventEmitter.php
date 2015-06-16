<?php
namespace Rally;

trait EventEmitter
{
    protected $callbacks = [];

    public function on($event, callable $callback)
    {
        if (!isset($this->callbacks[$event])) {
            $this->callbacks[$event] = [];
        }

        $this->callbacks[$event][] = $callback;
    }

    public function emit($event, ...$args)
    {
        if (!isset($this->callbacks[$event])) {
            return;
        }

        foreach ($this->callbacks[$event] as $callback) {
            $callback(...$args);
        }
    }
}
