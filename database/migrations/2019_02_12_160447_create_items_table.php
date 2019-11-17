<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id')->comment('discord bot related to the item');
            $table->string('sku')->unique()->comment('unique item code from xsolla');
            $table->string('name');
            $table->string('image_url');
            $table->integer('price')->comment('item price in points');
            $table->boolean('enabled')->default(true)->comment('whether the item is on sale');
            $table->boolean('consumable')->default(true);
            $table->integer('expiration_time')->nullable()->comment('expiration time in seconds (NULL means permanent)');
            $table->integer('purchase_limit')->nullable()->comment('max purchase count per user (NULL means infinity)');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
