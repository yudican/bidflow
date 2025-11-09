<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignUuid('received_by')->nullable()->after('created_by');
            $table->foreignUuid('rejected_by')->nullable()->after('received_by');
            $table->text('rejected_reason')->nullable()->after('rejected_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('received_by');
            $table->dropColumn('rejected_by');
            $table->dropColumn('rejected_reason');
        });
    }
};
