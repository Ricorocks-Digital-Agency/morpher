<?php


namespace RicorocksDigitalAgency\Morpher\Tests\examples\Morphs;


use Exception;
use RicorocksDigitalAgency\Morpher\Morph;

class AnonymousMorph extends Morph
{
    protected static $migration = "0000_00_00_000002_create_anonymous_table";

    public function run()
    {
        throw new Exception("The morph did actually run!");
    }
}
