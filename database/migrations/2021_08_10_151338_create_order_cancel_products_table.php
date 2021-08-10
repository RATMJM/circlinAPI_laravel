<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderCancelProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_cancel_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('order_product_id')->constrained();
            $table->integer('qty');
            $table->string('reason')->comment('취소 사유');
            $table->string('status')->default('request')->comment('상태 (request|complete)');
            $table->timestamp('canceled_at')->nullable()->comment('취소 접수 거절 일시');
            $table->timestamp('completed_at')->nullable()->comment('취소 완료 일시');
        });

        $comment = "고객접수(created_at) - 완료(completed_at)";
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE order_cancel_products comment '$comment'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_cancel_products');
    }
}
