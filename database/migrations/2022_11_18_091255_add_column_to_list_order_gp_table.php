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
        Schema::table('list_order_gp', function (Blueprint $table) {
            $table->string('vat_value')->nullable()->default(1.11)->after('tax_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('list_order_gp', function (Blueprint $table) {
            $table->dropColumn('vat_value');
        });
    }
};
