<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned();
            $table->bigInteger('seller_id')->unsigned();
            $table->bigInteger('seller_product_id')->unsigned(); 
            $table->bigInteger('size_availability_id')->unsigned();

            $table->integer('all_non_dispatched_status_quantity')->unsigned();
            $table->integer('to_be_purchased_quantity')->unsigned();
            $table->integer('to_be_received_quantity')->unsigned();
            $table->integer('received_quantity')->unsigned();
            $table->integer('received_incomplete_quantity')->unsigned();
            $table->integer('in_stock_quantity')->unsigned();
            $table->integer('to_be_packaged_quantity')->unsigned();
            $table->integer('packaged_quantity')->unsigned();
            $table->integer('to_be_dispatched_quantity')->unsigned();
            $table->bigInteger('dispatched_quantity')->unsigned();
            $table->timestamps();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('seller_id')->references('id')->on('sellers');
            $table->foreign('seller_product_id')->references('id')->on('product_seller');
            $table->foreign('size_availability_id')->references('id')->on('size_availabilities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
}
