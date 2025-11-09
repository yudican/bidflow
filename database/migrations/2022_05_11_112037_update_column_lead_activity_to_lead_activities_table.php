<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnLeadActivityToLeadActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->dateTime('start_date')->change();
            $table->dateTime('end_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->date('start_date');
            $table->date('end_date');
        });
    }
}
