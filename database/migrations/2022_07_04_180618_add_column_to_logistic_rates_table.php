<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToLogisticRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logistic_rates', function (Blueprint $table) {
            $table->integer('logistic_rate_original_id')->nullable();
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
            $table->dropColumn('logistic_rate_original_id');
        });
    }
}
