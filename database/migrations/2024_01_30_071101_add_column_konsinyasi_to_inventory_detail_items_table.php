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
        Schema::table('inventory_detail_items', function (Blueprint $table) {
            $table->foreignId('tax_id')->nullable();
            $table->double('tax_amount')->nullable();
            $table->double('tax_percentage')->nullable();
            $table->double('discount_percentage')->nullable();
            $table->integer('discount')->nullable()->default(0);
            $table->double('discount_amount')->nullable();
            $table->double('subtotal')->nullable();
            $table->double('price_nego')->nullable();
            $table->double('total')->nullable();
            $table->integer('stock_awal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_detail_items', function (Blueprint $table) {
            $table->dropColumn('tax_id');
            $table->dropColumn('tax_amount');
            $table->dropColumn('tax_percentage');
            $table->dropColumn('discount_percentage');
            $table->dropColumn('discount');
            $table->dropColumn('discount_amount');
            $table->dropColumn('subtotal');
            $table->dropColumn('price_nego');
            $table->dropColumn('total');
            $table->dropColumn('stock_awal');
        });
    }
};
