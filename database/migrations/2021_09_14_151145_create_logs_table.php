<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->comment('누가')->constrained();
            $table->ipAddress('ip');
            $table->string('type')->comment('어디서 무엇을');
            $table->foreignId('feed_id')->constrained();
            $table->foreignId('feed_comment_id')->constrained();
            $table->foreignId('mission_id')->constrained();
            $table->foreignId('mission_comment_id')->constrained();
            $table->foreignId('notice_id')->constrained();
            $table->foreignId('notice_comment_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
