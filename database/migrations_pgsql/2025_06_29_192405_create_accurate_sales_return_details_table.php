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
        Schema::create('accurate_sales_return_details', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sales_return_id')->nullable();
            $table->string('number')->nullable();

            // Item detail
            $table->decimal('unit_price', 20, 6)->default(0);
            $table->string('item_unit_name')->nullable();
            $table->string('item_name')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_field')->nullable();
            $table->string('item_type')->nullable();
            $table->string('warehouse_name')->nullable();
            $table->string('detail_name')->nullable();
            $table->decimal('qty', 20, 6)->default(0);

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
        Schema::dropIfExists('accurate_sales_return_details');
    }
};
