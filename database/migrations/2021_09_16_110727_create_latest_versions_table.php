<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLatestVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('latest_versions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('android')->comment('안드로이드 최신버전');
            $table->string('ios')->comment('IOS 최신버전');
            $table->string('comment')->comment('업데이트 내역');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('latest_versions');
    }
}
