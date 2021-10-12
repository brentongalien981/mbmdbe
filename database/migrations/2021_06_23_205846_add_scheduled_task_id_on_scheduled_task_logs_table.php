<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduledTaskIdOnScheduledTaskLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scheduled_task_logs', function (Blueprint $table) {
            $table->bigInteger('scheduled_task_id')->after('id')->unsigned()->nullable(); 
        });

        Schema::table('scheduled_task_logs', function (Blueprint $table) {
            $table->foreign('scheduled_task_id')->references('id')->on('scheduled_tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scheduled_task_logs', function (Blueprint $table) {
            $table->dropForeign('scheduled_task_id');
            $table->dropColumn('scheduled_task_id');
        });
    }
}
