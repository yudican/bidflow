<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfirmPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('confirm_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id');
            $table->string('nama_rekening');
            $table->string('jumlah_bayar');
            $table->dateTime('tanggal_bayar');
            $table->string('foto_struk')->nullable();
            $table->char('status', 1);
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
        Schema::dropIfExists('confirm_payments');
    }
}
