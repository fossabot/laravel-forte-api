<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('client_id')->comment('where the payment/refund is completed (xsolla)');
            $table->integer('user_item_id');
            $table->boolean('about_cash')->default(true)->comment('whether the payment/refund is relate to real cash (not points)');
            $table->boolean('refund')->default(true)->comment('whether the process is refund (not payment)');
            $table->integer('points_old');
            $table->integer('points_new');
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
        Schema::dropIfExists('receipts');
    }
}
