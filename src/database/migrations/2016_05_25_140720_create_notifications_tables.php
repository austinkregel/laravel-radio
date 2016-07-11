<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTables extends Migration
{

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('radio_notifications', function(Blueprint $table){
            $table->increments('id');
            $table->integer('channel_id')->unsigned();
            $table->integer('user_id')->index();
            $table->integer('is_unread')->index();
            $table->text('name');
            $table->text('description');
            $table->text('link')->nullable();
            $table->timestamps();
        });

        Schema::create('radio_channels', function(Blueprint $table){
            $table->increments('id');
            $table->string('uuid', 36)->index();
            $table->string('type', 10)->index();
            $table->timestamps();
        });

        Schema::create('radio_channel_user', function(Blueprint $table){
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('channel_id')->unsigned();
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
        Schema::drop('radio_notifications');
        Schema::drop('radio_channels');
        Schema::drop('radio_channel_user');
        //
    }

}
