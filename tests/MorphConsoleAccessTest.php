<?php


namespace RicorocksDigitalAgency\Morpher\Tests;

use CreateConsoleTable;
use Illuminate\Support\Facades\Event;
use RicorocksDigitalAgency\Morpher\Morph;
use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RicorocksDigitalAgency\Morpher\Support\Console;

class MorphConsoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_global_console_is_available_to_each_morph()
    {
        // We pass the global console to this mock morph so that it can evaluate the console instance it has
        $this->app->bind(
            EvaluatesConsoles::class,
            fn($app) => new EvaluatesConsoles($app->make(Console::class))
        );

        Event::dispatch(new MigrationStarted(app(CreateConsoleTable::class), 'up'));
        Event::dispatch(new MigrationEnded(app(CreateConsoleTable::class), 'up'));
    }
}

class EvaluatesConsoles extends Morph
{
    protected static $migration = CreateConsoleTable::class;
    protected $parentConsole;

    public function __construct($console = null)
    {
        $this->parentConsole = $console;
    }

    public function canRun()
    {
        return true;
    }

    public function run()
    {
        expect($this->console)->toBe($this->parentConsole);
    }
}
