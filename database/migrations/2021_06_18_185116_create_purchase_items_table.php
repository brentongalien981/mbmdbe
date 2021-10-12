<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_id')->unsigned();
            $table->bigInteger('seller_product_id')->unsigned(); 
            $table->bigInteger('size_availability_id')->unsigned();
            $table->integer('quantity')->unsigned();
            $table->decimal('actual_price', 9, 2)->nullable();
            $table->decimal('projected_price', 9, 2);
            $table->bigInteger('status_code');
            $table->timestamps();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreign('purchase_id')->references('id')->on('purchases');
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
        Schema::dropIfExists('purchase_items');
    }
}
