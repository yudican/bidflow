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
        Schema::create('accurate_sales_order_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sales_order_id');
            $table->string('sales_order_po_number')->nullable();

            $table->string('char_field2')->nullable();
            $table->string('date_field1')->nullable();
            $table->string('tax_1_amount')->nullable();
            $table->string('po_number')->nullable();
            $table->string('status')->nullable();
            $table->string('status_name')->nullable();
            $table->string('description')->nullable();
            $table->string('trans_date')->nullable();
            $table->string('approval_status')->nullable();

            $table->string('unit_name')->nullable();
            $table->string('default_warehouse_do_name')->nullable();
            $table->string('salesman_name')->nullable();

            $table->string('item_no')->nullable();
            $table->string('item_name')->nullable();
            $table->string('item_char_field1')->nullable();
            $table->string('item_type')->nullable();

            $table->decimal('unit_price', 20, 2)->default(0);
            $table->decimal('quantity', 20, 2)->default(0);
            $table->decimal('total_price', 20, 2)->default(0);

            $table->string('warehouse_name')->nullable();
            $table->string('project_name')->nullable();
            $table->timestamps();

            $table->foreign('sales_order_id')
                  ->references('id')
                  ->on('accurate_sales_order')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accurate_sales_order_details');
    }
};
