<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWaypointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('waypoints', function (Blueprint $table) {
            $table->integer('route_id');
            $table->decimal('lat', 9, 6);
            $table->decimal('lon', 9, 6);
            $table->integer('position'); // How to enforce uniqueness within the block? 
            $table->longText('description')->nullable();
            $table->string('tag')->nullable();
            $table->string('address')->nullable();

            $table->foreign('route_id')
                  ->references('id')->on('routes')
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
        Schema::dropIfExists('waypoints');
    }
}
