<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeteranganToLogApproveFinanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_approve_finance', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_approve_finance', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
}
