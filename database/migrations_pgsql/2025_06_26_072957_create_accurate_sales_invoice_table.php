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
        Schema::create('accurate_sales_invoice', function (Blueprint $table) {
            $table->id();
            $table->string('approval_status')->nullable(); // approvalStatus
            $table->date('due_date')->nullable();          // dueDate
            $table->string('tax_number')->nullable();      // taxNumber
            $table->text('description')->nullable();       // description
            $table->date('ship_date')->nullable();         // shipDate
            $table->decimal('tax1_amount', 15, 3)->default(0);   // tax1Amount
            $table->string('number')->nullable();          // number
            $table->decimal('total_amount', 15, 3)->default(0);  // totalAmount
            $table->date('trans_date')->nullable();        // transDate
            $table->string('char_field1')->nullable();     // charField1
            $table->string('po_number')->nullable();       // poNumber

            // Customer details
            $table->unsignedBigInteger('customer_id')->nullable();      // customer.id
            $table->string('customer_name')->nullable();                // customer.name
            $table->string('customer_no')->nullable();                  // customer.customerNo

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
        Schema::dropIfExists('accurate_sales_invoice');
    }
};
