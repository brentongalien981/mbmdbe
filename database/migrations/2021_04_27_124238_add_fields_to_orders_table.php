<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('charged_subtotal', 8, 2)->after('email')->nullable();
            $table->decimal('charged_shipping_fee', 8, 2)->after('charged_subtotal')->nullable();
            $table->decimal('charged_tax', 8, 2)->after('charged_shipping_fee')->nullable();

            $table->timestamp('earliest_delivery_date')->after('charged_tax')->nullable();
            $table->timestamp('latest_delivery_date')->after('earliest_delivery_date')->nullable();

            $table->unsignedTinyInteger('projected_total_delivery_days')->after('latest_delivery_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('charged_subtotal');
            $table->dropColumn('charged_shipping_fee');
            $table->dropColumn('charged_tax');
            $table->dropColumn('projected_total_delivery_days');
        });
    }
}
