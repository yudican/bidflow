<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderToLeadActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->string('reminder')->nullable()->after('geo_tagging');
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
            $table->dropColumn('reminder');
        });
    }
}
