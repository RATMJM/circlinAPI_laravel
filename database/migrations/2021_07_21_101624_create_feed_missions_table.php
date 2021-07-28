<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedMissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feed_missions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('feed_id')->constrained();
            $table->foreignId('mission_id')->constrained();
            $table->foreignId('mission_stats_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feed_missions');
    }
}
