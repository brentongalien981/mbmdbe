<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncompleteOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incomplete_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->string('user_id', 64);
            $table->string('order_id', 128)->nullable();
            $table->string('stripe_payment_intent_id', 128);
            $table->bigInteger('result_code');
            $table->string('entire_process_logs', 2048)->nullable();
            $table->timestamps();
        });


        Schema::table('incomplete_orders', function (Blueprint $table) {

            $table->foreign('cart_id')->references('id')->on('carts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incomplete_orders', function (Blueprint $table) {

            $table->dropForeign(['cart_id']);
        });

        Schema::dropIfExists('incomplete_orders');
    }
}
