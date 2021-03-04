<?php

namespace RicorocksDigitalAgency\Morpher\Providers;

use Illuminate\Support\ServiceProvider;
use RicorocksDigitalAgency\Morpher\Commands\MakeCommand;
use RicorocksDigitalAgency\Morpher\Morpher;

class MorpherServiceProvider extends ServiceProvider
{
    public function register()
    {
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
        app('morpher')->setup();
        $this->publishes([__DIR__ . '/../../config/morpher.php' => config_path('morpher.php')], 'morpher');
    }
}
