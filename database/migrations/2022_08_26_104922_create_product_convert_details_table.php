<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductConvertDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_convert_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_convert_id')->references('id')->on('product_converts')->onDelete('cascade');
            $table->string('sku')->nullable();
            $table->string('produk_nama')->nullable();
            $table->bigInteger('qty')->nullable();
            $table->bigInteger('toko')->nullable();
            $table->bigInteger('harga_awal')->nullable();
            $table->bigInteger('harga_promo')->nullable();
            $table->bigInteger('harga_satuan')->nullable();
            $table->bigInteger('ongkir')->nullable();
            $table->dateTime('tanggal_transaksi')->nullable();
            $table->string('subtotal')->nullable();
            $table->char('status_convert', 1)->nullable()->default(1);
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
        Schema::dropIfExists('product_convert_details');
    }
}
