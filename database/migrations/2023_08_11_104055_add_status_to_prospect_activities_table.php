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
        Schema::table('prospect_activities', function (Blueprint $table) {
            $table->string('status')->nullable()->after('submit_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prospect_activities', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
