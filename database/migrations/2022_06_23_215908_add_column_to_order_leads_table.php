<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToOrderLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_leads', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable();
            $table->string('order_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('shipping_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_leads', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
            $table->dropColumn('order_number');
            $table->dropColumn('invoice_number');
            $table->dropColumn('reference_number');
            $table->dropColumn('shipping_type');
        });
    }
}
