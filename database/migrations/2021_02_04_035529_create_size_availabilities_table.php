<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSizeAvailabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('size_availabilities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('seller_product_id')->unsigned();
            $table->string('size', 8);
            $table->smallInteger('quantity');
            $table->timestamps();
        });


        Schema::table('size_availabilities', function (Blueprint $table) {

            $table->foreign('seller_product_id')->references('id')->on('product_seller');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('size_availabilities', function (Blueprint $table) {
        //     $table->dropForeign(['seller_product_id']);
            
        // });

        Schema::dropIfExists('size_availabilities');
    }
}
