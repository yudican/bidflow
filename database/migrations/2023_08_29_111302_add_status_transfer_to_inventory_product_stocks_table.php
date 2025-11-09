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
            //post_ethix
            $table->char('post_ethix', 1)->nullable()->default(0);
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
            //
            $table->dropColumn('post_ethix');
        });
    }
};
