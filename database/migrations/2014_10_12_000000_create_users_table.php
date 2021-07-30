<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('family_name')->nullable();
            $table->string('given_name')->nullable();
            $table->string('name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable()->comment('이메일 인증 일시');
            $table->string('password');
            $table->string('phone')->nullable();
            $table->timestamp('phone_verified_at')->nullable()->comment('휴대폰 인증 일시');
            $table->integer('point')->default(0);
            $table->string('profile_image')->nullable()->comment('프로필 사진');
            $table->string('greeting')->nullable()->comment('인사말');
            $table->string('area_code')->nullable()->comment('사는 곳');
            $table->timestamp('tutorial_completed_at')->nullable();
            $table->softDeletes();
            $table->string('device_type')->nullable()->comment('접속한 기기 종류(android/iphone)');
            $table->string('device_token')->nullable()->comment('푸시 전송 토큰');
            $table->string('access_token')->nullable();
            $table->string('refresh_token')->nullable();
            $table->string('refresh_token_expire_in')->nullable();
            $table->timestamp('last_login_at')->nullable()->comment('마지막 로그인 시점');
            $table->string('last_login_ip')->nullable()->comment('마지막 로그인 IP');
            $table->boolean('agree1')->default(false)->comment('서비스 이용약관 동의');
            $table->boolean('agree2')->default(false)->comment('개인정보 수집 및 이용약관 동의');
            $table->boolean('agree3')->default(false)->comment('위치정보 이용약관 동의');
            $table->boolean('agree4')->default(false)->comment('이메일 마케팅 동의');
            $table->boolean('agree5')->default(false)->comment('SMS 마케팅 동의');
            $table->float('lat')->nullable()->comment('위도');
            $table->float('lng')->nullable()->comment('경도');
        });

        $comment = "고객";
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users comment '$comment'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
