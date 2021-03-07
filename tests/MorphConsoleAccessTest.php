<?php


namespace RicorocksDigitalAgency\Morpher\Tests;

use Exception;
use CreateConsoleTable;
use Illuminate\Support\Facades\Event;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Events\MigrationEnded;
use Symfony\Component\Console\Input\ArgvInput;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class MorphConsoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function if_there_is_a_commandStarting_event_the_output_instance_is_available_to_a_morph()
    {
        Event::dispatch(new CommandStarting(
            'migrate',
            new ArgvInput(),
            new RunsCallbackOnWriteLn(function() {
                throw new Exception('Woah there!');
            })
        ));

        $this->expectExceptionMessage('Woah there!');

        // The AccessesConsole/WritesLn example morph is tied to the CreateConsoleTable migration
        // On run it will call writeLn on the available console instance in the Morph
        // The console output given above runs the closure when writeLn is called
        // So we should see the above closure run, and the exception thrown

        Event::dispatch(new MigrationStarted(app(CreateConsoleTable::class), 'up'));
        Event::dispatch(new MigrationEnded(app(CreateConsoleTable::class), 'up'));
    }
}

class RunsCallbackOnWriteLn implements OutputInterface
{
    protected $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function write($messages, bool $newline = false, int $options = 0) { }

    public function writeln($messages, int $options = 0)
    {
        value($this->callback);
    }

    public function setVerbosity(int $level) { }

    public function getVerbosity() { }

    public function isQuiet() { }

    public function isVerbose() { }

    public function isVeryVerbose() { }

    public function isDebug() { }

    public function setDecorated(bool $decorated) { }

    public function isDecorated() { }

    public function setFormatter(OutputFormatterInterface $formatter) { }

    public function getFormatter() { }

}
