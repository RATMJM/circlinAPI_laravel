<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('ctg_lg');
            $table->string('name_lg');
            $table->string('ctg_md')->nullable();
            $table->string('name_md')->nullable();
            $table->string('ctg_sm')->nullable();
            $table->string('name_sm')->nullable();
            $table->string('name_en');
            $table->string('lat_md')->nullable();
            $table->string('lng_md')->nullable();
            $table->string('lat_sm')->nullable();
            $table->string('lng_sm')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('areas');
    }
}
