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
        Schema::table('mp_order_lists', function (Blueprint $table) {
            $table->string('gp_number')->nullable();
            $table->string('status_ethix')->default('notsubmited')->nullable();
            $table->string('status_gp')->default('notsubmited')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mp_order_lists', function (Blueprint $table) {
            $table->dropColumn('gp_number');
            $table->dropColumn('status_ethix');
            $table->dropColumn('status_gp');
        });
    }
};
