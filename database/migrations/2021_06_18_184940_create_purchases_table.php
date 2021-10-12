<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('seller_id')->unsigned(); 
            $table->decimal('projected_subtotal', 9, 2)->nullable();
            $table->decimal('projected_shipping_fee', 9, 2)->nullable();
            $table->decimal('projected_other_fee', 9, 2)->nullable();
            $table->decimal('projected_tax', 9, 2)->nullable();

            $table->decimal('charged_subtotal', 9, 2)->nullable();
            $table->decimal('charged_shipping_fee', 9, 2)->nullable();
            $table->decimal('charged_other_fee', 9, 2)->nullable();
            $table->decimal('charged_tax', 9, 2)->nullable();

            $table->bigInteger('status_code');
            $table->timestamp('estimated_delivery_date')->nullable();

            $table->string('order_id_from_seller_site', 128)->nullable();
            $table->string('shipping_id_from_carrier', 128)->nullable();
            $table->string('notes', 1024)->nullable();
            $table->timestamps();
        });

        Schema::table('purchases', function (Blueprint $table) {
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
        Schema::dropIfExists('purchases');
    }
}
