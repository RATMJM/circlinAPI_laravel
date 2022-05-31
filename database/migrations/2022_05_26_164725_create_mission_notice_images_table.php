<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionNoticeImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_notice_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_notice_id')->constrained();
            $table->timestamps();
            $table->string('type')->comment('이미지인지 비디오인지 (image / video)');
            $table->string('image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_notice_images');
    }
}
