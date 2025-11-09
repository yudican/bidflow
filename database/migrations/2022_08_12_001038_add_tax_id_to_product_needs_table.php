<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxIdToProductNeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_needs', function (Blueprint $table) {
            $table->foreignId('tax_id')->nullable();
            $table->foreignId('discount_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_needs', function (Blueprint $table) {
            $table->dropColumn('tax_id');
            $table->dropColumn('discount_id');
        });
    }
}
