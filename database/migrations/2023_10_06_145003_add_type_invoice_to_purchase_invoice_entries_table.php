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
            $table->string('type_invoice')->default('product')->after('status');
            $table->string('notes')->default('product')->after('type_invoice');
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
            $table->dropColumn('type_invoice');
            $table->dropColumn('notes');
        });
    }
};
