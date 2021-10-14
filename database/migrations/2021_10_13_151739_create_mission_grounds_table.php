<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionGroundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_grounds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('mission_id')->constrained();
            $table->string('intro_video')->nullable()->comment('소개 페이지 상단 동영상');

            $table->string('logo_image')->nullable()->comment('상단 로고');
            $table->string('code_title')->nullable()->comment('입장코드 라벨');
            $table->string('code')->nullable()->comment('입장코드 (있으면 비교, 없으면 입력 받기만)');
            $table->string('code_image')->nullable()->comment('입장코드 룸 상단 이미지');
            $table->json('goal_distances')->nullable()->comment('참가하기 전 설정할 목표 거리 (km)');

            $table->string('ground_title')->default('운동장')->comment('운동장 탭 타이틀');
            $table->string('record_title')->default('내기록')->comment('내기록 탭 타이틀');
            $table->string('cert_title')->default('이벤트')->comment('인증서 탭 타이틀');
            $table->string('feeds_title')->nullable()->comment('피드 탭 타이틀 (null 일 경우 노출 X)');

            // 운동장
            $table->string('ground_background_image')->nullable()->comment('운동장 탭 배경이미지');
            $table->json('ground_ai_text')->nullable()->comment('운동장 탭 조건별 텍스트');

            $table->string('ground_progress_type')->comment('운동장 탭 진행상황 타입 (feed/distance)');
            $table->string('ground_progress_background_image')->comment('운동장 탭 진행상황 배경이미지');
            $table->string('ground_progress_image')->comment('운동장 탭 진행상황 차오르는 이미지');
            $table->string('ground_progress_title')->comment('운동장 탭 진행상황 타이틀');
            $table->string('ground_progress_text')->default('{%feeds_count}')->comment('운동장 탭 진행상황 텍스트');

            $table->string('ground_box_users_count_text')->default('{%users_count}명')->comment('운동장 탭 참가중인 유저 수 텍스트');
            $table->string('ground_box_users_count_title')->comment('운동장 탭 참가중인 유저 수 타이틀');
            $table->string('ground_box_summary_type')->default('feed')->comment('feed/today_feed/distance/today_distance');
            $table->string('ground_box_summary_text')->default('{%feeds_count}')->comment('운동장 탭 피드 수 텍스트');
            $table->string('ground_box_summary_title')->comment('운동장 탭 피드 수 타이틀');

            $table->string('ground_users_type')->default('recent_bookmark')->comment('운동장 탭 유저 목록 타입');
            $table->string('ground_users_title')->comment('운동장 탭 유저 목록 타이틀');
            $table->string('ground_users_text')->comment('운동장 탭 유저 목록 비었을 때 텍스트');

            // 내기록
            $table->string('record_background_image')->nullable()->comment('내기록 탭 배경이미지');
            $table->json('record_ai_text')->nullable()->comment('내기록 탭 조건별 텍스트');

            $table->tinyInteger('record_progress_image_count')->default(9)->comment('내기록 탭 진행상황 뱃지 개수');
            $table->json('record_progress_images')->comment('내기록 탭 진행상황 이미지');
            $table->string('record_progress_title')->comment('내기록 탭 진행상황 타이틀');
            $table->string('record_progress_text')->default('{%remaining_day}')->comment('내기록 탭 진행상황 텍스트');
            $table->string('record_progress_description')->nullable()->comment('내기록 탭 진행상황 텍스트 옆 설명');

            $table->boolean('record_box_is_show')->default(true)->comment('내기록 탭 박스 노출 여부');
            $table->json('record_box_left_type')->nullable()->comment('내기록 탭 박스 왼쪽 타입');
            $table->json('record_box_left_title')->nullable()->comment('내기록 탭 박스 왼쪽 타이틀');
            $table->json('record_box_left_text')->nullable()->comment('내기록 탭 박스 왼쪽 텍스트');
            $table->json('record_box_center_type')->nullable()->comment('내기록 탭 박스 가운데 타입');
            $table->json('record_box_center_title')->nullable()->comment('내기록 탭 박스 가운데 타이틀');
            $table->json('record_box_center_text')->nullable()->comment('내기록 탭 박스 가운데 텍스트');
            $table->json('record_box_right_type')->nullable()->comment('내기록 탭 박스 오른쪽 타입');
            $table->json('record_box_right_title')->nullable()->comment('내기록 탭 박스 오른쪽 타이틀');
            $table->json('record_box_right_text')->nullable()->comment('내기록 탭 박스 오른쪽 텍스트');
            $table->string('record_box_description')->nullable()->comment('내기록 탭 박스 하단 설명');

            // 이벤트
            $table->string('cert_subtitle')->default('모바일 인증서')->comment('인증서 탭 인증서 타이틀');
            $table->string('cert_description')->nullable()->comment('인증서 탭 내용');
            $table->string('cert_background_image')->comment('인증서 탭 인증서 배경이미지');
            $table->string('cert_disabled_text')->comment('인증서 탭 비활성화 상태 멘트');
            $table->tinyInteger('cert_enabled_feeds_count')->default(1)->comment('인증서 탭 인증서 활성화될 피드 수');

            $table->string('feeds_filter_title')->comment('전체 피드 탭 필터 타이틀');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_grounds');
    }
}
