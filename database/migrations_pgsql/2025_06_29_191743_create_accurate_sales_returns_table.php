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
        Schema::create('accurate_sales_returns', function (Blueprint $table) {
            $table->id(); // Primary key Laravel
            $table->unsignedBigInteger('id_sales_return')->nullable(); // ID return dari Accurate

            // Customer info
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_code')->nullable();

            // Invoice & Return Info
            $table->string('number')->nullable(); // Nomor return
            $table->unsignedBigInteger('invoice_id')->nullable(); // ID invoice yg direturn
            $table->string('return_status_type')->nullable(); // Status return (e.g. Return, Refund)
            $table->decimal('unit_price', 20, 6)->default(0); // Harga per unit
            $table->decimal('sub_total', 20, 6)->default(0); // Subtotal sebelum pajak/diskon
            $table->decimal('return_amount', 20, 6)->default(0); // Jumlah return
            $table->decimal('total_amount', 20, 6)->default(0); // Total keseluruhan

            // Metadata
            $table->text('description')->nullable(); // Catatan return
            $table->date('trans_date')->nullable(); // Tanggal transaksi
            $table->string('approval_status')->nullable(); // Status approval
            $table->string('created_by')->nullable(); // User Accurate

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accurate_sales_returns');
    }
};
