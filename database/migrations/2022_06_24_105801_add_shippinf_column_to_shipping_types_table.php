<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippinfColumnToShippingTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_types', function (Blueprint $table) {
            $table->boolean('shipping_logo')->nullable();
            $table->boolean('shipping_service')->nullable();
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
            $table->dropColumn('shipping_logo');
            $table->dropColumn('shipping_service');
        });
    }
}
