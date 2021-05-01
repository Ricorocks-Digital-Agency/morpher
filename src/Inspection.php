<?php


namespace RicorocksDigitalAgency\Morpher;


class Inspection
{
    public $beforeHooks;
    public $afterHooks;

    public function __construct()
    {
        $this->beforeHooks = collect();
        $this->afterHooks = collect();
    }

    public function before(callable $closure)
    {
        $this->beforeHooks->push($closure);
        return $this;
    }

    public function after(callable $closure)
    {
        $this->afterHooks->push($closure);
        return $this;
    }

    public function runBefore(Morph $morph)
    {
        $this->beforeHooks->each(fn($hook) => call_user_func($hook, $morph));
    }

    public function runAfter(Morph $morph)
    {
        $this->afterHooks->each(fn($hook) => call_user_func($hook, $morph));
    }
}
