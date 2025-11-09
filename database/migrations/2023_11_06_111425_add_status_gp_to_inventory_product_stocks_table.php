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
        Schema::table('inventory_product_stocks', function (Blueprint $table) {
            $table->string('status_gp')->nullable();
            $table->string('gp_transfer_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_product_stocks', function (Blueprint $table) {
            $table->dropColumn('status_gp');
            $table->dropColumn('gp_transfer_number');
        });
    }
};
