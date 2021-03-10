<?php

namespace RicorocksDigitalAgency\Morpher;

use RicorocksDigitalAgency\Morpher\Support\Console;

abstract class Morph
{
    protected static $migration;
    protected Console $console;

    public function withConsole(Console $console)
    {
        $this->console = $console;
    }

    public function prepare()
    {
    }

    public function canRun()
    {
        return true;
    }

    public static function migration()
    {
        return static::$migration;
    }

    public abstract function run();
}
