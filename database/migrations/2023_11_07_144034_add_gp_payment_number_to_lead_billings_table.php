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
        Schema::table('lead_billings', function (Blueprint $table) {
            $table->string('gp_payment_number')->nullable();
            $table->string('status_gp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_billings', function (Blueprint $table) {
            $table->dropColumn('gp_payment_number');
            $table->dropColumn('status_gp');
        });
    }
};
