<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEthixMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ethix_masters', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->nullable();
            $table->string('do_number')->nullable();
            $table->string('so_number')->nullable();
            $table->string('sku')->nullable();
            $table->string('qty')->nullable();
            $table->string('sn_parent')->nullable();
            $table->string('sn_child')->nullable();
            $table->date('exp_date')->nullable();
            $table->string('awb_number')->nullable();
            $table->string('recipient_info')->nullable();
            $table->string('status_so')->nullable();
            $table->date('status_date')->nullable();
            $table->string('status_code')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ethix_masters');
    }
}
