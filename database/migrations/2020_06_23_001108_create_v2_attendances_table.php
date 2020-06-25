<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2AttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('discord_id')->unique();
            $table->integer('key_count')->default(1)->comment('키 획득 count');
            $table->json('key_acquired_at')->comment('키 획득 일');
            $table->json('box_unpacked_at')->nullable()->comment('상자 개봉 일');
            $table->timestamps();

            $table->foreign('discord_id')
                ->references('discord_id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_attendances');
    }
}
