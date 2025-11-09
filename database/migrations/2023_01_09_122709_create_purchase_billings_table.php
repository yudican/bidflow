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
        Schema::create('purchase_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->nullable();
            $table->foreignUuid('created_by')->nullable();
            $table->foreignUuid('approved_by')->nullable();
            $table->foreignUuid('rejected_by')->nullable();
            $table->string('nama_bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('nama_pengirim')->nullable();
            $table->string('jumlah_transfer')->nullable();
            $table->string('bukti_transfer')->nullable();
            $table->char('status', 1)->nullable()->default('0'); // 0 = pending, 1 = approved, 2 = rejected
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
        Schema::dropIfExists('purchase_billings');
    }
};
