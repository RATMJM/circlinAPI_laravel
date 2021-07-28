<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventMissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_missions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->comment('이벤트 미션 제작자')->constrained();
            $table->foreignId('mission_id')->constrained();
            $table->string('title')->comment('이벤트 제목');
            $table->text('content')->comment('이벤트 상세 내용');
            $table->string('thumbnail_image')->comment('썸네일');
            $table->text('detail_images')->comment('상세 설명 이미지');
            $table->timestamp('reserve_start_date')->nullable()->comment('이벤트 참가 예약 시작 일시');
            $table->timestamp('start_date')->nullable()->comment('이벤트 시작 일시');
            $table->timestamp('end_date')->nullable()->comment('이벤트 종료 일시');
            $table->integer('user_limit')->default(0)->comment('최대 참여자 수 (0은 무제한)');
            $table->boolean('is_show')->default(true)->comment('노출 여부');
            $table->integer('event_order')->default(0)->comment('이벤트 페이지 정렬 (0은 노출 X)');
            $table->integer('reward_carepoint')->comment('이벤트 성공 보상');
            $table->integer('week_duration')->comment('총 주차');
            $table->integer('week_min_count')->comment('주당 최소 인증 횟수');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_missions');
    }
}
