<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_categories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('mission_category_id')->nullable()->constrained();
            $table->string('title');
            $table->string('emoji')->comment('타이틀 앞의 이모지');
            $table->text('description')->comment('카테고리 설명');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_categories');
    }
}
