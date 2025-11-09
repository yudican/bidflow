<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductImportTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_import_temps', function (Blueprint $table) {
            $table->id();
            $table->string('trx_id')->nullable();
            $table->string('user')->nullable();
            $table->string('channel')->nullable();
            $table->string('toko')->nullable();
            $table->string('sku')->nullable();
            $table->string('produk_nama')->nullable();
            $table->bigInteger('harga_awal')->nullable();
            $table->bigInteger('harga_promo')->nullable();
            $table->bigInteger('qty')->nullable();
            $table->bigInteger('ongkir')->nullable();
            $table->string('metode_pembayaran')->nullable();
            $table->bigInteger('diskon')->nullable();
            $table->string('tanggal_transaksi')->nullable();
            $table->string('kurir')->nullable();
            $table->string('resi')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('product_import_temps');
    }
}
