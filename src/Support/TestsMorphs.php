<?php


namespace RicorocksDigitalAgency\Morpher\Support;


use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

trait TestsMorphs
{
    public function supportMorphs()
    {
        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:fresh');
            $this->app[Kernel::class]->setArtisan(null);

            $this->artisan('migrate:rollback');

            RefreshDatabaseState::$migrated = false;
        });
    }
}
