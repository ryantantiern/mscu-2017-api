<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friends', function (Blueprint $table) {
           $table->increments('id');
           $table->integer('user_id');
           $table->integer('friend_id');
           $table->boolean('accepted')->default(0);

           
           $table->foreign('user_id')
           ->references('id')
           ->on('users')
           ->onDelete('cascade');

           $table->foreign('friend_id')
           ->references('id')
           ->on('users')
           ->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('friends');
    }
}
