<?php

use Illuminate\Support\Facades\DB;
use RicorocksDigitalAgency\Morpher\Morph;

class PrepareExampleMorph extends Morph
{
    protected static $migration = CreateAnotherExampleTable::class;
    protected $values;

    public function prepare()
    {
        $this->values = DB::table('examples')->select('name')->get();
    }

    public function run()
    {
        DB::table('other_examples')->insert($this->values->map(fn($data) => ['name' => $data->name])->toArray());
    }
}
