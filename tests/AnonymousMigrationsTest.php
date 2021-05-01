<?php


namespace RicorocksDigitalAgency\Morpher\Tests;


use Exception;

class AnonymousMigrationsTest extends TestCase
{
    /** @test */
    public function morphs_can_hook_into_anonymous_migrations()
    {
        // We expect an exception because the AnonymousMorph throws one. As such, we know it ran successfully.
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("This exception came from an anonymous migration morph");

        $this->loadMigrationsFrom(__DIR__ . '/examples/migrations/automated');
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback', ['--database' => 'testbench'])->run();
        });
    }

}
