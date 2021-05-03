<?php


namespace RicorocksDigitalAgency\Morpher;


class Inspection
{
    public $beforeMigratingHooks;
    public $beforeHooks;
    public $afterHooks;

    public function __construct()
    {
        $this->beforeMigratingHooks = collect();
        $this->beforeHooks = collect();
        $this->afterHooks = collect();
    }

    public function beforeThisMigration(callable $closure): self
    {
        return tap($this, fn() => $this->beforeMigratingHooks->push($closure));
    }

    public function before(callable $closure): self
    {
        return tap($this, fn() => $this->beforeHooks->push($closure));
    }

    public function after(callable $closure): self
    {
        return tap($this, fn() => $this->afterHooks->push($closure));
    }

    public function runBeforeThisMigration(Morph $morph)
    {
        $this->beforeMigratingHooks->each(fn($hook) => call_user_func($hook, $morph));
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
