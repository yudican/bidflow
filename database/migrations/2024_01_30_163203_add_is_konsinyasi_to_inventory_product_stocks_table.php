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
            $table->string('is_konsinyasi', 10)->nullable();
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
            $table->dropColumn('is_konsinyasi');
        });
    }
};
