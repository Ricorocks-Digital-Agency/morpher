<?php


namespace RicorocksDigitalAgency\Morpher;


class Inspection
{
    public $beforeHook;
    public $afterHook;

    public function before(callable $closure)
    {
        $this->beforeHook = $closure;
        return $this;
    }

    public function after(callable $closure)
    {
        $this->afterHook = $closure;
        return $this;
    }

    public function runBefore(Morph $morph)
    {
        if (!$this->beforeHook) {
            return;
        }

        call_user_func($this->beforeHook, $morph);
    }

    public function runAfter(Morph $morph)
    {
        if (!$this->afterHook) {
            return;
        }

        call_user_func($this->afterHook, $morph);
    }
}
