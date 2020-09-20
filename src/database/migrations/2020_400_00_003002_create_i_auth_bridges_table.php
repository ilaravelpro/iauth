<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 11:10 AM
 * Copyright (c) 2020. Powered by iamir.net
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIAuthBridgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_auth_bridges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('session_id')->unsigned();
            $table->foreign('session_id')->references('id')->on('i_auth_sessions')->onDelete('cascade');
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
        Schema::dropIfExists('i_auth_bridges');
    }
}
