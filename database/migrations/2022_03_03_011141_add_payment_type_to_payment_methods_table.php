<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentTypeToPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('payment_channel')->nullable()->after('payment_type');
            $table->string('payment_code')->nullable()->after('payment_channel');
            $table->string('payment_va_number')->nullable()->after('payment_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('payment_type');
            $table->dropColumn('payment_code');
            $table->dropColumn('payment_va_number');
        });
    }
}
