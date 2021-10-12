<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_seller_id')->after('product_id')->nullable();
            $table->unsignedBigInteger('size_availability_id')->after('product_seller_id')->nullable();
        });

        Schema::table('order_items', function (Blueprint $table) {

            $table->foreign('product_seller_id')->references('id')->on('product_seller');
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
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('product_seller_id');
            $table->dropColumn('size_availability_id');
        });
    }
}
