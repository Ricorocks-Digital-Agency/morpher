<?php

namespace RicorocksDigitalAgency\Morpher\Tests;

use RicorocksDigitalAgency\Morpher\Providers\MorpherServiceProvider;
use Spatie\LaravelRay\RayServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set(
            'database.connections.testbench',
            [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            RayServiceProvider::class,
            MorpherServiceProvider::class,
        ];
    }

}
