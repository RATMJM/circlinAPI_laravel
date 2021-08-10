<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderReturnProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_return_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('order_product_id')->constrained();
            $table->integer('qty');
            $table->string('reason')->comment('반품 사유');
            $table->string('status')->default('request')->comment('상태 (request|receive|complete)');
            $table->timestamp('canceled_at')->nullable()->comment('취소 접수 거절 일시');
            $table->timestamp('requested_at')->nullable()->comment('반품 접수 일시');
            $table->timestamp('received_at')->nullable()->comment('반품 회수 일시');
            $table->timestamp('completed_at')->nullable()->comment('반품 완료 일시');
        });

        $comment = "고객접수(created_at) - 써클인접수(requested_at) - 회수(received_at) - 완료(completed_at)";
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE order_return_products comment '$comment'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_return_products');
    }
}
