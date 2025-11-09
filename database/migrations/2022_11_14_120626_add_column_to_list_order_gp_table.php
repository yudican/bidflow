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
            $table->string('tax_name')->nullable()->after('status');
            $table->string('tax_value')->nullable()->after('tax_name');
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
            $table->dropColumn('tax_name');
            $table->dropColumn('tax_value');
        });
    }
};
