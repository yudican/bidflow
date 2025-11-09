<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('amount_to_pay')->nullable()->after('status');
            $table->foreignId('address_user_id')->nullable()->after('amount_to_pay');
            $table->string('payment_va_number')->nullable()->after('address_user_id');
            $table->string('payment_token')->nullable()->after('payment_va_number');
            $table->string('payment_unique_code')->nullable()->after('payment_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('amount_to_pay');
            $table->dropColumn('address_user_id');
            $table->dropColumn('payment_va_number');
            $table->dropColumn('payment_token');
            $table->dropColumn('payment_unique_code');
        });
    }
}
