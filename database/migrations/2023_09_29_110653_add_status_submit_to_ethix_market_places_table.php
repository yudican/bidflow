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
        Schema::table('ethix_market_places', function (Blueprint $table) {
            $table->string('total_price')->nullable()->after('total_discount');
            $table->enum('status_submit', ['submited', 'notsubmited'])->nullable()->default('notsubmited')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ethix_market_places', function (Blueprint $table) {
            $table->dropColumn('total_price');
            $table->dropColumn('status_submit');
        });
    }
};
