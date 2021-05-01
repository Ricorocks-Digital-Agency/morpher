<?php


namespace RicorocksDigitalAgency\Morpher\Facades;

use RicorocksDigitalAgency\Morpher\Inspection;
use Illuminate\Support\Facades\Facade;

/**
 * Class Morpher
 * @package RicorocksDigitalAgency\Morpher\Facades
 *
 * @method static Inspection test(string $morph) Start an inspection on a Morph class for testing
 * @method static void setup() Boot the event listeners for Morpher
 */
class Morpher extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'morpher';
    }

}
