<?php

namespace RicorocksDigitalAgency\Morpher\Support;

use Illuminate\Foundation\Testing\RefreshDatabase;

trait TestsMorphs
{
    use RefreshDatabase;

    public function refreshDatabase()
    {
        $this->beforeApplicationDestroyed(function() {
            $this->usingInMemoryDatabase()
                ? $this->refreshInMemoryDatabase()
                : $this->refreshTestDatabase();
        });
    }
}
