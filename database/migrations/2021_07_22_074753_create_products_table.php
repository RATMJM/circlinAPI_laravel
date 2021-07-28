<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('code');
            $table->string('name_ko');
            $table->string('name_en');
            $table->foreignId('brand_id')->constrained();
            $table->boolean('is_show')->default(true);
            $table->string('status')->default('sale')->comment('현재 상태 (sale / soldout)');
            $table->integer('price')->comment('정상가');
            $table->integer('sale_price')->comment('판매가');
            $table->integer('shipping_fee')->comment('배송비');
            $table->string('thumbnail_image');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
