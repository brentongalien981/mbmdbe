<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyStatsDefaultValuesForTableInventoryItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->integer('all_non_dispatched_status_quantity')->unsigned()->default(0)->change();
            $table->integer('to_be_purchased_quantity')->unsigned()->default(0)->change();
            $table->integer('to_be_received_quantity')->unsigned()->default(0)->change();
            $table->integer('received_quantity')->unsigned()->default(0)->change();
            $table->integer('received_incomplete_quantity')->unsigned()->default(0)->change();
            $table->integer('in_stock_quantity')->unsigned()->default(0)->change();
            $table->integer('to_be_packaged_quantity')->unsigned()->default(0)->change();
            $table->integer('packaged_quantity')->unsigned()->default(0)->change();
            $table->integer('to_be_dispatched_quantity')->unsigned()->default(0)->change();
            $table->bigInteger('dispatched_quantity')->unsigned()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
