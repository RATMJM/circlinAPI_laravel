<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionRankUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_rank_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_rank_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->integer('feeds_count')->unsigned();
            $table->integer('summation')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_rank_users');
    }
}
