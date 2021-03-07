<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsoleTable extends Migration {

    public function up()
    {
        Schema::create('console', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('console', function (Blueprint $table) {
            $table->dropIfExists();
        });
    }

}
