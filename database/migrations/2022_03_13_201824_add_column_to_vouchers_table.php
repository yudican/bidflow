<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('type')->nullable();
            $table->integer('total_point')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('total_point');
        });
    }
}
