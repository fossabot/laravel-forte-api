<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('transaction_id')->default(null)->comment('xsolla payment duplicate observe transaction id');
            $table->unsignedInteger('client_id')->comment('where the payment/refund is completed (xsolla)');
            $table->unsignedInteger('user_item_id')->nullable();
            $table->boolean('about_cash')->default(true)->comment('whether the payment/refund is relate to real cash (not points)');
            $table->boolean('refund')->default(true)->comment('whether the process is refund (not payment)');
            $table->bigInteger('points_old');
            $table->bigInteger('points_new');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('user_item_id')->references('id')->on('user_items');
            $table->foreign('user_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('receipts');
    }
}
