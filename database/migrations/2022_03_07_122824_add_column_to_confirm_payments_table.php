<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToConfirmPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('confirm_payments', function (Blueprint $table) {
            $table->string('bank_tujuan')->nullable()->after('nama_rekening');
            $table->string('bank_dari')->nullable()->after('bank_tujuan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('confirm_payments', function (Blueprint $table) {
            $table->dropColumn('bank_tujuan');
            $table->dropColumn('bank_dari');
        });
    }
}
