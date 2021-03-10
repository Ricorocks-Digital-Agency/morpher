<?php

namespace RicorocksDigitalAgency\Morpher\Providers;

use Illuminate\Support\ServiceProvider;
use RicorocksDigitalAgency\Morpher\Morpher;
use RicorocksDigitalAgency\Morpher\Facades\Morph;
use RicorocksDigitalAgency\Morpher\Support\Console;
use Symfony\Component\Console\Output\ConsoleOutput;
use RicorocksDigitalAgency\Morpher\Commands\MakeCommand;

class MorpherServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Console::class, fn() => new Console(new ConsoleOutput));
        $this->app->singleton('morpher', Morpher::class);
    }

    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/morpher.php', 'morpher');

        if ($this->app->runningInConsole()) {
            $this->console();
        }
    }

    protected function console()
    {
        $this->commands(MakeCommand::class);
        Morph::setup();
        $this->publishes([__DIR__ . '/../../config/morpher.php' => config_path('morpher.php')], 'morpher');
    }
}
