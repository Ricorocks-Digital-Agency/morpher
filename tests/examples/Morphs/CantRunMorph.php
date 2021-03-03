<?php

use RicorocksDigitalAgency\Morpher\Morph;

class CantRunMorph extends Morph
{
    protected static $migration = CreateExampleTable::class;

    public function canRun()
    {
        return false;
    }

    public function run()
    {
        throw new Exception("This should never have run!");
    }
}
