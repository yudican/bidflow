<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToRefundMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refund_masters', function (Blueprint $table) {
            $table->char('status', 1)->default(0)->after('transfer_photo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('refund_masters', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
