<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderExchangeProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_exchange_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('order_product_id')->constrained();
            $table->integer('qty');
            $table->string('reason')->comment('교환 사유');
            $table->string('status')->default('request')->comment('상태 (request|receive|complete)');
            $table->timestamp('received_at')->nullable()->comment('교환 접수 일시');
            $table->timestamp('completed_at')->nullable()->comment('회수 완료 일시');
            $table->foreignId('redelivery_id')->references('id')->on('order_product_deliveries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_exchange_products');
    }
}
