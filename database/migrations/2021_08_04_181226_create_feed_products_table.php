<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feed_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('feed_id')->constrained();
            $table->string('type')->comment('내부상품인지 외부상품인지 (inside / outside)');
            $table->foreignId('product_id')->nullable()->constrained();
            $table->string('image_url')->nullable();
            $table->string('brand_title')->nullable();
            $table->string('product_title')->nullable();
            $table->integer('price')->nullable();
            $table->string('product_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feed_products');
    }
}
