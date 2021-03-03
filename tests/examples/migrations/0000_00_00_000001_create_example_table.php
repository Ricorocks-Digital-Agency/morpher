<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateExampleTable extends Migration {

    public function up()
    {
        Schema::create('examples', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('examples', function (Blueprint $table) {
            $table->dropIfExists();
        });
    }

}
