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
            $table->timestamp('canceled_at')->nullable()->comment('취소 접수 거절 일시');
            $table->timestamp('received_at')->nullable()->comment('교환 접수 일시');
            $table->timestamp('completed_at')->nullable()->comment('회수 완료 일시');
            $table->foreignId('redelivery_id')->references('id')->on('order_product_deliveries');
        });

        $comment = "고객접수(created_at) - 써클인접수(received_at) - 회수완료(completed_at) - 재배송(redelivery_id) - 완료(completed_at)";
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE order_exchange_products comment '$comment'");
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
