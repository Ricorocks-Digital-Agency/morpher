<?php

namespace RicorocksDigitalAgency\Morpher;

abstract class Morph
{
    protected static $migration;

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
