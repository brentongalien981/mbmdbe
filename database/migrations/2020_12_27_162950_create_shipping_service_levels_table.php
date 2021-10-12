<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingServiceLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_service_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('alternate_name', 64)->nullable();
            $table->string('description', 256)->nullable();
            $table->tinyInteger('latest_delivery_days');
            $table->string('carrier', 64);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_service_levels');
    }
}
