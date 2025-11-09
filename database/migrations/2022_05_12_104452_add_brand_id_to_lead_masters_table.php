<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandIdToLeadMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_masters', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('sales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_masters', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
        });
    }
}
