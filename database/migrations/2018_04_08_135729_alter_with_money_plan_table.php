<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWithMoneyPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('withmoneyplan', function (Blueprint $table) {
            //
            $table->unsignedInteger('last_id');
            $table->timestamp('invalid_time');
            $table->timestamp('start_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('withmoneyplan', function (Blueprint $table) {
            //
        });
    }
}
