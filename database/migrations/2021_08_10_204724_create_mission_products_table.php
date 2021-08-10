<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('mission_id')->constrained();
            $table->string('type')->comment('내부상품인지 외부상품인지 (inside|outside)');
            $table->foreignId('product_id')->nullable()->constrained();
            $table->string('image_url')->nullable();
            $table->string('brand_title')->nullable();
            $table->string('product_title')->nullable();
            $table->integer('price')->nullable();
            $table->string('product_url')->nullable();
        });

        $comment = "미션 준비물 테이블";
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE mission_products comment '$comment'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_products');
    }
}
