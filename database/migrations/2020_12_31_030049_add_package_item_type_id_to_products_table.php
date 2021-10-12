<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPackageItemTypeIdToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->bigInteger('package_item_type_id')->unsigned()->after('quantity')->nullable();
        });

        Schema::table('products', function (Blueprint $table) {

            $table->foreign('package_item_type_id')->references('id')->on('package_item_types');
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
            $table->dropForeign(['package_item_type_id']);
            $table->dropColumn('package_item_type_id');
            
        });
    }
}
