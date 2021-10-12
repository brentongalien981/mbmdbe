<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFrontendPseudoExpiresInToBmdAuthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bmd_auths', function (Blueprint $table) {
            $table->string('frontend_pseudo_expires_in', 32)->after('refresh_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bmd_auths', function (Blueprint $table) {
            $table->dropColumn('frontend_pseudo_expires_in');
        });
    }
}
