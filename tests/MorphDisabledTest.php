<?php


namespace RicorocksDigitalAgency\Morpher\Tests;


use CreateExampleTable;
use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class MorphDisabledTest extends TestCase
{
    use RefreshDatabase;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('morpher.enabled', false);
    }

    /** @test */
    public function it_does_not_run_when_disabled()
    {
        app(CreateExampleTable::class)->up();

        DB::table('examples')->insert([['name' => 'Bob'], ['name' => 'Barry']]);

        Event::dispatch(new MigrationEnded(app(CreateExampleTable::class), 'up'));

        expect(DB::table('examples')->find(1)->name)->toEqual('Bob');
        expect(DB::table('examples')->find(2)->name)->toEqual('Barry');
    }

}
