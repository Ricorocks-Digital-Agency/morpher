<?php


namespace RicorocksDigitalAgency\Morpher\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * Class Morpher
 * @package RicorocksDigitalAgency\Morpher\Facades
 *
 * @method static test(string $morph) Start an inspection on a Morph class for testing
 * @method static setup() Boot the event listeners for Morpher
 */
class Morph extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'morpher';
    }

}
