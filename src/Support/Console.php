<?php

namespace RicorocksDigitalAgency\Morpher\Support;

use Illuminate\Support\Facades\Event;
use Illuminate\Console\Events\CommandStarting;
use Symfony\Component\Console\Output\OutputInterface;

class Console
{
    public OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        Event::listen(CommandStarting::class, fn($event) => $this->output = $event->output);
    }

    public function info($message)
    {
        $this->output->writeln("<info>$message</>");
    }

    public function warning($message)
    {
        $this->output->writeln("<fg=red>$message</>");
    }

    public function error($message)
    {
        $this->output->writeln("<error>$message</>");
    }
}
