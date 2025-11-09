<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShipperToTransactionAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_agents', function (Blueprint $table) {
            $table->unsignedBigInteger('shipper_address_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_agents', function (Blueprint $table) {
            $table->dropColumn('shipper_address_id');
        });
    }
}
