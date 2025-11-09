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
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('result');
            $table->string('longitude')->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }
};
