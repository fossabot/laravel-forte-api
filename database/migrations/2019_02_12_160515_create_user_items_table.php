<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('item_id');
            $table->boolean('expired')->default(false)->comment('whether expiration time has passed or the cash was refunded');
            $table->boolean('consumed')->default(false);
            $table->boolean('sync')->default(false)->comment('whether bot(items.client_id) is notified of the change in this item');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items');
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
        Schema::dropIfExists('user_items');
    }
}
