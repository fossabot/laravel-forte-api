<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXsollaUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsolla_urls', function (Blueprint $table) {
            $table->increments('id');
            $table->text('token');
            $table->text('redirect_url');
            $table->integer('hit');
            $table->boolean('expired');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsolla_urls');
    }
}
