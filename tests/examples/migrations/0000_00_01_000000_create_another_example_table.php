<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAnotherExampleTable extends Migration {

    public function up()
    {
        Schema::create('other_examples', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('other_examples', function (Blueprint $table) {
            $table->dropIfExists();
        });
    }

}
