<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 9:53 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIAuthSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('i_auth_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model')->nullable();
            $table->bigInteger('model_id')->nullable();
            $table->string('key', 110)->index();
            $table->string('value', 110)->nullable();
            $table->string('session', 50)->index();
            $table->string('token')->nullable();
            $table->boolean('revoked')->default(0);
            $table->boolean('verified')->default(0);
            $table->text('meta')->nullable();
            $table->timestamp('expired_at')->nullable();
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
        Schema::dropIfExists('i_auth_sessions');
    }
}
