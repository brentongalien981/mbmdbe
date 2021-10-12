<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductSellerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_seller', function (Blueprint $table) {
            $table->bigInteger('product_id')->unsigned();
            $table->bigInteger('seller_id')->unsigned();
            $table->decimal('buy_price', 8, 2);
            $table->decimal('sell_price', 8, 2);
            $table->integer('quantity')->nullable();
            $table->timestamps();
        });

        Schema::table('product_seller', function (Blueprint $table) {

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('seller_id')->references('id')->on('sellers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_seller');
    }
}
