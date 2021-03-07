<?php

use RicorocksDigitalAgency\Morpher\Morph;

class WritesLn extends Morph
{
    protected static $migration = CreateConsoleTable::class;

    public function canRun()
    {
        return true;
    }

    public function run()
    {
        $this->console->writeLn('hey');
    }
}
