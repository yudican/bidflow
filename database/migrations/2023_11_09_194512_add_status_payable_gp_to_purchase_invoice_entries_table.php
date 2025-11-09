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
        Schema::table('purchase_invoice_entries', function (Blueprint $table) {
            $table->string('gp_payable_number')->nullable();
            $table->string('status_payable_gp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_invoice_entries', function (Blueprint $table) {
            $table->dropColumn('gp_payable_number');
            $table->dropColumn('status_payable_gp');
        });
    }
};
