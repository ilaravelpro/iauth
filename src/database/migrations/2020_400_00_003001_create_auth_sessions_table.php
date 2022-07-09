<?php


/**
 * Author: Amir Hossein Jahani | iAmir.net
 * Last modified: 9/16/20, 9:53 PM
 * Copyright (c) 2020. Powered by iamir.net
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('creator_id')->nullable()->unsigned();
            $table->string('model')->nullable();
            $table->bigInteger('model_id')->nullable();
            $table->string('key', 110)->nullable()->index();
            $table->string('value', 110)->nullable();
            $table->string('session', 50)->index();
            $table->string('token')->nullable();
            $table->boolean('revoked')->default(0);
            $table->boolean('verified')->default(0);
            $table->text('ip')->nullable();
            $table->longText('meta')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });

        Schema::table('auth_sessions', function($table) {
            $table->foreign('creator_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_sessions');
    }
}
