<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticeCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice_comments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('notice_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->integer('group')->default(0);
            $table->tinyInteger('depth')->default(0);
            $table->text('comment');
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
        Schema::dropIfExists('notice_comments');
    }
}
