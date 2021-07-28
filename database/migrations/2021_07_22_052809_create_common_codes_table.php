<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommonCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('common_codes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('ctg_lg')->nullable();
            $table->string('ctg_md')->nullable();
            $table->string('ctg_sm');
            $table->string('content');
            $table->string('content_en');
            $table->string('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('common_codes');
    }
}
