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
            $table->unsignedBigInteger('master_bin_id')->nullable();
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
            $table->dropColumn('master_bin_id');
        });
    }
};
