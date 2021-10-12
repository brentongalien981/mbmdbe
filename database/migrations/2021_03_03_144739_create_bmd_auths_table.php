<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBmdAuthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bmd_auths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('token', 1024);
            $table->string('refresh_token', 1024)->nullable();
            $table->string('expires_in', 32);
            $table->unsignedBigInteger('auth_provider_type_id');
            $table->string('oauth_external_user_id', 128)->nullable();
            $table->timestamps();
        });


        Schema::table('bmd_auths', function (Blueprint $table) {

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('auth_provider_type_id')->references('id')->on('auth_provider_types');
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

            $table->dropForeign(['user_id']);
            $table->dropForeign(['auth_provider_type_id']);
        });

        Schema::dropIfExists('bmd_auths');
    }
}
