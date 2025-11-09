<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToShippingTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_types', function (Blueprint $table) {
            $table->integer('shipping_discount')->nullable()->default(0)->after('shipping_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipping_types', function (Blueprint $table) {
            $table->dropColumn('shipping_discount');
        });
    }
}
