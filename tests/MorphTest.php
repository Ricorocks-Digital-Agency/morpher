<?php

namespace RicorocksDigitalAgency\Morpher\Tests;

use App\Morphs\ExampleMorph;
use CreateAnotherExampleTable;
use CreateExampleTable;
use Exception;
use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use RicorocksDigitalAgency\Morpher\Morph;

class MorphTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        include_once __DIR__ . '/examples/migrations/0000_00_00_000001_create_example_table.php';
        include_once __DIR__ . '/examples/migrations/0000_00_01_000000_create_another_example_table.php';
    }

    /** @test */
    public function it_runs_the_voyage_when_the_migration_has_completed()
    {
        app(CreateExampleTable::class)->up();

        // Add some fake data to change
        DB::table('examples')->insert([['name' => 'Bob'], ['name' => 'Barry']]);

        Event::dispatch(new MigrationEnded(app(CreateExampleTable::class), 'up'));

        expect(DB::table('examples')->find(1)->name)->toEqual('Foo');
        expect(DB::table('examples')->find(2)->name)->toEqual('Foo');
    }

    /** @test */
    public function it_doesnt_run_on_down_ended_events()
    {
        app(CreateExampleTable::class)->down();
        Event::dispatch(new MigrationEnded(app(CreateExampleTable::class), 'down'));

        expect(Schema::hasTable('examples'))->toBeFalse();
    }

    /** @test */
    public function it_runs_a_prepare_method_prior_to_migrating()
    {
        app(CreateExampleTable::class)->up();
        DB::table('examples')->insert([['name' => 'Bob'], ['name' => 'Barry']]);


        Event::dispatch(new MigrationStarted(app(CreateAnotherExampleTable::class), 'up'));
        app(CreateAnotherExampleTable::class)->up();
        Event::dispatch(new MigrationEnded(app(CreateAnotherExampleTable::class), 'up'));

        expect(DB::table('other_examples')->find(1)->name)->toEqual('Bob');
        expect(DB::table('other_examples')->find(2)->name)->toEqual('Barry');
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set(
            'morpher.paths',
            [
                __DIR__ . '/examples/Morphs'
            ]
        );
    }
}
