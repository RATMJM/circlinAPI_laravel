<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('send_user_id')->references('id')->on('users');
            $table->foreignId('receive_user_id')->references('id')->on('users');
            $table->string('type')->comment('알림 구분 (출력 내용은 common_codes)');
            $table->foreignId('feed_id')->nullable()->comment('알림 발생한 게시물')->constrained();
            $table->string('status')->comment('알림 상태 (보냄 / 수신 / 읽음 등으로)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
