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
        Schema::table('product_needs', function (Blueprint $table) {
            $table->bigInteger('qty_dibayar')->after('qty_delivery')->default(0);
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
            $table->dropColumn('qty_dibayar');
        });
    }
};
