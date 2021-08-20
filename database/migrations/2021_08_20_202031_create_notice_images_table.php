<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticeImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice_images', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('notice_id')->constrained();
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
        Schema::dropIfExists('notice_images');
    }
}
