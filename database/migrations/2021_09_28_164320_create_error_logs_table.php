<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErrorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('client_time')->nullable();
            $table->foreignId('user_id')->nullable()->comment('누가')->constrained();
            $table->string('type')->comment('에러 발생 플랫폼 (front, back)');
            $table->string('message')->nullable();
            $table->text('stack_trace')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('error_logs');
    }
}
