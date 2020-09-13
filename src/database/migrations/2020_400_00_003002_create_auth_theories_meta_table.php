<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthTheoriesMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_theories_meta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('theory_id')->unsigned()->nullable();
            $table->foreign('theory_id')->references('id')->on('auth_theories')->onDelete('cascade');
            $table->string('key')->nullable();
            $table->string('value')->nullable();
            $table->string('type')->default('null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_theories_meta');
    }
}
