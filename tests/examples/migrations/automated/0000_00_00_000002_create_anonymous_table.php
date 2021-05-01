<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {

    public function up()
    {
        Schema::create('anonymous_table', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('anonymous_table', function (Blueprint $table) {
            $table->dropIfExists();
        });
    }

};
