<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeCustomerToOrderManualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_manuals', function (Blueprint $table) {
            $table->string('type_customer')->nullable();
            $table->foreignId('warehouse_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_manuals', function (Blueprint $table) {
            $table->dropColumn('type_customer');
            $table->dropColumn('warehouse_id');
        });
    }
}
