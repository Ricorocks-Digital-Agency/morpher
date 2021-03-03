<?php

use Illuminate\Support\Facades\DB;
use RicorocksDigitalAgency\Morpher\Morph;

class ExampleMorph extends Morph
{
    protected static $migration = CreateExampleTable::class;

    public function run()
    {
        DB::table('examples')->where('id', '>', 0)->update(['name' => 'Foo']);
    }
}
