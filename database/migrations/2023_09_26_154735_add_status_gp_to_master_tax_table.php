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
        Schema::table('master_tax', function (Blueprint $table) {
            $table->char('gp_status', 1)->nullable()->default(0)->after('tax_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_tax', function (Blueprint $table) {
            $table->dropColumn('gp_status');
        });
    }
};
