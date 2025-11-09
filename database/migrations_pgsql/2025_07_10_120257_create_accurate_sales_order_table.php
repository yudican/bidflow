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
        Schema::create('accurate_sales_order', function (Blueprint $table) {
            $table->bigInteger('id')->primary(); // ID dari Accurate
            $table->string('number')->nullable();
            $table->string('char_field1')->nullable();
            $table->date('ship_date')->nullable();
            $table->string('po_number')->nullable();
            $table->string('status')->nullable();
            $table->string('status_name')->nullable();
            $table->date('trans_date')->nullable();
            $table->string('approval_status')->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_amount', 20, 2)->default(0);

            $table->bigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_no')->nullable();

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
        Schema::dropIfExists('accurate_sales_order');
    }
};
