<?php


namespace RicorocksDigitalAgency\Morpher\Tests;


use CreateExampleTable;
use ExampleMorph;
use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use RicorocksDigitalAgency\Morpher\Facades\Morpher;

class MorphInspectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_run_an_inspection_prior_to_running_the_morph()
    {
        Morpher::test(ExampleMorph::class)->before(function() {
            expect(DB::table('examples')->find(1)->name)->toEqual('Bob');
            expect(DB::table('examples')->find(2)->name)->toEqual('Barry');
        });

        app(CreateExampleTable::class)->up();
        // Add some fake data to change
        DB::table('examples')->insert([['name' => 'Bob'], ['name' => 'Barry']]);

        Event::dispatch(new MigrationEnded(app(CreateExampleTable::class), 'up'));
    }
    
    /** @test */
    public function it_can_run_an_inspection_after_running_the_morph() 
    {
        Morpher::test(ExampleMorph::class)->after(function() {
            expect(DB::table('examples')->find(1)->name)->toEqual('Foo');
            expect(DB::table('examples')->find(2)->name)->toEqual('Foo');
        });

        app(CreateExampleTable::class)->up();
        // Add some fake data to change
        DB::table('examples')->insert([['name' => 'Bob'], ['name' => 'Barry']]);

        Event::dispatch(new MigrationEnded(app(CreateExampleTable::class), 'up'));
    }

    /** @test */
    public function multiple_before_inspections_on_the_same_morph_can_be_run()
    {
        $this->expectException(\Exception::class);

        Morpher::test(ExampleMorph::class)->before(function() {
            throw new \Exception("This should throw");
        });

        Morpher::test(ExampleMorph::class)->before(function() {
            expect(DB::table('examples')->find(1)->name)->toEqual('Bob');
        });

        app(CreateExampleTable::class)->up();
        DB::table('examples')->insert([['name' => 'Bob'], ['name' => 'Barry']]);

        Event::dispatch(new MigrationEnded(app(CreateExampleTable::class), 'up'));
    }

    /** @test */
    public function multiple_after_inspections_on_the_same_morph_can_be_run()
    {
        $this->expectException(\Exception::class);

        Morpher::test(ExampleMorph::class)->after(function() {
            throw new \Exception("This should throw");
        });

        Morpher::test(ExampleMorph::class)->after(function() {
            expect(DB::table('examples')->find(1)->name)->toEqual('Foo');
        });

        app(CreateExampleTable::class)->up();
        DB::table('examples')->insert([['name' => 'Bob'], ['name' => 'Barry']]);

        Event::dispatch(new MigrationEnded(app(CreateExampleTable::class), 'up'));
    }

}
