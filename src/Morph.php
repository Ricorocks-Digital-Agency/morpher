<?php

namespace RicorocksDigitalAgency\Morpher;

use Symfony\Component\Console\Output\OutputInterface;

abstract class Morph
{
    protected static $migration;
    protected OutputInterface $console;

    public function withConsole(OutputInterface $console)
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
