<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsShippingWaivedToProductSellerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_seller', function (Blueprint $table) {
            $table->decimal('discount_sell_price', 8, 2)->nullable()->after('sell_price');;
            $table->tinyInteger('restock_days')->after('quantity')->nullable();
            $table->boolean('is_shipping_waived')->default(0)->after('restock_days');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_seller', function (Blueprint $table) {
            //
        });
    }
}
