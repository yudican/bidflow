<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionToUserVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });
    }
}
