<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDailyResetQuantityOnSizeAvailabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('size_availabilities', function (Blueprint $table) {
            $table->integer('daily_reset_quantity')->after('quantity')->unsigned()->default(0); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('size_availabilities', function (Blueprint $table) {
            $table->dropColumn('daily_reset_quantity');
        });
    }
}
