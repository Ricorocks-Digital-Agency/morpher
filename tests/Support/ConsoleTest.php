<?php

namespace RicorocksDigitalAgency\Morpher\Tests\Support;

use Illuminate\Support\Facades\Event;
use Illuminate\Console\Events\CommandStarting;
use Symfony\Component\Console\Input\ArgvInput;
use RicorocksDigitalAgency\Morpher\Tests\TestCase;
use RicorocksDigitalAgency\Morpher\Support\Console;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

class ConsoleTest extends TestCase
{
    /** @test */
    public function by_default_the_OutputInterface_injected_will_be_used_for_the_console_output()
    {
        $console = new Console(new RunsCallbackOnWriteLn(fn() => expect(true)->toBeTrue()));

        $console->info('Hey there!');
    }

    /** @test */
    public function if_there_is_a_command_starting_event_this_output_is_used_instead()
    {
        $console = new Console(new RunsCallbackOnWriteLn(fn() => $this->fail('This console instance was not overwritten')));

        Event::dispatch(new CommandStarting(
            'migrate',
            new ArgvInput(),
            new RunsCallbackOnWriteLn(fn() => expect(true)->toBeTrue())
        ));

        $console->info('Hey there!');
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

    public function getVerbosity(): int { }

    public function isQuiet(): bool { }

    public function isVerbose(): bool { }

    public function isVeryVerbose(): bool { }

    public function isDebug(): bool { }

    public function setDecorated(bool $decorated) { }

    public function isDecorated(): bool { }

    public function setFormatter(OutputFormatterInterface $formatter) { }

    public function getFormatter(): OutputFormatterInterface { }

}
