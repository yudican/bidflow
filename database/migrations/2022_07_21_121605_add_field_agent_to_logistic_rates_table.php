<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldAgentToLogisticRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logistic_rates', function (Blueprint $table) {
            $table->boolean('logistic_agent_status')->nullable()->default(true)->after('logistic_rate_status');
            $table->boolean('logistic_custommer_status')->nullable()->default(true)->after('logistic_agent_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('logistic_rates', function (Blueprint $table) {
            $table->dropColumn('logistic_agent_status');
            $table->dropColumn('logistic_custommer_status');
        });
    }
}
