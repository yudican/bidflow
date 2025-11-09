<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTopToLeadMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_masters', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable();
            $table->foreignId('payment_term')->nullable();
            $table->string('order_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('reference_number')->nullable();
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
            //
        });
    }
}
