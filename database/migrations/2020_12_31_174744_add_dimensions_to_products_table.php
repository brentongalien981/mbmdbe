<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDimensionsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // dimensions are in inches
        // weight is in ounce
        Schema::table('products', function (Blueprint $table) {
            $table->float('length', 4, 1)->after('package_item_type_id')->nullable();
            $table->float('width', 4, 1)->after('length')->nullable();
            $table->float('height', 4, 1)->after('width')->nullable();
            $table->float('weight', 4, 1)->after('height')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('length');
            $table->dropColumn('width');
            $table->dropColumn('height');
            $table->dropColumn('weight');
        });
    }
}
