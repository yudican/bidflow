<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductVariantIdToProductMarginBottomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_margin_bottoms', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_margin_bottoms', function (Blueprint $table) {
            $table->dropColumn('product_variant_id');
        });
    }
}
