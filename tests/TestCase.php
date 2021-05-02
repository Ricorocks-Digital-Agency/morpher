<?php

namespace RicorocksDigitalAgency\Morpher\Tests;

use RicorocksDigitalAgency\Morpher\Providers\MorpherServiceProvider;
use Spatie\LaravelRay\RayServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        include_once __DIR__ . '/examples/migrations/0000_00_00_000001_create_example_table.php';
        include_once __DIR__ . '/examples/migrations/0000_00_01_000000_create_another_example_table.php';
        include_once __DIR__ . '/examples/migrations/0000_00_01_000000_create_console_table.php';
    }

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

        $app['config']->set('morpher.paths', [__DIR__ . '/examples/Morphs']);
    }

    protected function getPackageProviders($app)
    {
        return [
            RayServiceProvider::class,
            MorpherServiceProvider::class,
        ];
    }

}
