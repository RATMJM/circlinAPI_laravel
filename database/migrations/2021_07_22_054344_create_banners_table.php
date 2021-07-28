<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('banner_type')->comment('어디에 노출되는 광고인지 (float/ad)');
            $table->integer('sort_num')->default(0)->comment('정렬 순서 (높을수록 우선)');
            $table->string('name')->comment('배너명');
            $table->string('description')->comment('배너 상세설명');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('banner_image');
            $table->string('link_type');
            $table->foreignId('product_id')->nullable();//->constrained();
            $table->foreignId('notice_id')->nullable()->constrained();
            $table->string('link_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banners');
    }
}
