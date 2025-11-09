<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusPenagihanToOrderLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_leads', function (Blueprint $table) {
            $table->string('status_penagihan')->nullable();
            $table->string('status_invoice')->nullable();
            $table->date('due_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_leads', function (Blueprint $table) {
            $table->dropColumn('status_penagihan');
            $table->dropColumn('status_invoice');
            $table->dropColumn('due_date');
        });
    }
}
