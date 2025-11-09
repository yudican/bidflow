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
        Schema::create('purchase_invoice_entries', function (Blueprint $table) {
            $table->id();
            $table->string('received_number');
            $table->string('vendor_doc_number');
            $table->date('invoice_date')->nullable();
            $table->foreignUuid('created_by');
            $table->string('vendor_id')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('batch_id')->nullable();
            $table->string('payment_term_id')->nullable();
            $table->boolean('submit_gp')->nullable();
            $table->char('status', 1)->nullable();
            $table->foreignId('company_id', 1)->nullable();
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
        Schema::dropIfExists('purchase_invoice_entries');
    }
};
