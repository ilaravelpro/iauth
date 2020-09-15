<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthBridgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_bridges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('theory_id')->unsigned();
            $table->foreign('theory_id')->references('id')->on('auth_theories')->onDelete('cascade');
            $table->string('method', 100);
            $table->string('pin')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
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
        Schema::dropIfExists('auth_bridges');
    }
}
