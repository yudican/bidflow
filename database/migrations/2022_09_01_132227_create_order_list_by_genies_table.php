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
        Schema::create('order_list_by_genies', function (Blueprint $table) {
            $table->id();
            $table->string('trx_id')->nullable();
            $table->string('user')->nullable();
            $table->string('channel')->nullable();
            $table->string('store')->nullable();
            $table->string('sku')->nullable();
            $table->string('nama_produk')->nullable();
            $table->string('harga_awal')->nullable()->default(0);
            $table->string('harga_promo')->nullable()->default(0);

            $table->string('qty')->nullable()->default(1);
            $table->string('nominal')->nullable()->default(0);
            $table->string('ongkir')->nullable()->default(0);
            $table->string('pajak')->nullable()->default(0);
            $table->string('asuransi')->nullable()->default(0);
            $table->string('total_diskon')->nullable()->default(0);
            $table->string('biaya_komisi')->nullable()->default(0);
            $table->string('biaya_layanan')->nullable()->default(0);

            $table->string('ongkir_dibayar_sistem')->nullable()->default(0);
            $table->string('potongan_harga')->nullable()->default(0);
            $table->string('subsidi_angkutan')->nullable()->default(0);
            $table->string('koin')->nullable()->default(0);
            $table->string('loin_cashback')->nullable()->default(0);
            $table->string('jumlah_pengambalian_dana')->nullable()->default(0);
            $table->string('voucher_channel')->nullable();
            $table->string('diskon_penjual')->nullable()->default(0);

            $table->string('biaya_lacanan_kartu_kredit')->nullable()->default(0);
            $table->string('metode_pembayaran')->nullable();
            $table->string('diskon')->nullable()->default(0);
            $table->string('tanggal_transaksi')->nullable();
            $table->string('kurir')->nullable();
            $table->string('resi')->nullable();
            $table->string('status')->nullable();
            $table->string('status_pengiriman')->nullable();
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
        Schema::dropIfExists('order_list_by_genies');
    }
};
