<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPackagingRelatedColumnsToInventoryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->integer('being_packaged_quantity')->after('to_be_packaged_quantity')->unsigned()->default(0); 
            $table->integer('with_order_item_problems_quantity')->after('dispatched_quantity')->unsigned()->default(0);
            $table->integer('with_order_problems_quantity')->after('with_order_item_problems_quantity')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('being_packaged_quantity');
            $table->dropColumn('with_order_item_problems_quantity');
            $table->dropColumn('with_order_problems_quantity');
        });
    }
}
