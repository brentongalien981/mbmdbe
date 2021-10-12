<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduledTaskStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_task_statuses', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('code', 8);
            $table->string('name', 64);
            $table->string('readable_name', 128)->nullable();
            $table->string('description', 256)->nullable();

            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scheduled_task_statuses');
    }
}
