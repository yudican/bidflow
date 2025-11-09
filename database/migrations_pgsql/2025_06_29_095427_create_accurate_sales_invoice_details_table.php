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
        Schema::create('accurate_sales_invoice_details', function (Blueprint $table) {
            $table->id(); // primary key

            // ID dari detail invoice Accurate
            $table->unsignedBigInteger('accurate_detail_id')->nullable(); // dari "id"

            // Relasi ke tabel invoice utama
            $table->unsignedBigInteger('invoice_id')->nullable(); // relasi ke accurate_sales_invoice

            // Informasi umum
            $table->string('invoice_number')->nullable(); // "number"
            $table->string('char_field1')->nullable(); // "charField1"
            $table->string('branch_name')->nullable(); // "branchName"

            // Detail Sales Order (detailExpense)
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->string('sales_order_number')->nullable();

            // Detail Barang (detailItem)
            $table->unsignedBigInteger('delivery_order_detail_id')->nullable();
            $table->string('item_no')->nullable();
            $table->string('item_name')->nullable();
            $table->string('item_code')->nullable(); // charField1
            $table->decimal('unit_price', 20, 6)->default(0);
            $table->decimal('quantity', 20, 6)->default(0);
            $table->string('unit_name')->nullable();

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
        Schema::dropIfExists('accurate_sales_invoice_details');
    }
};
