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
        Schema::connection('pgsql')->create('tbl_stock_opname_customer', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uid')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->string('sku', 100);
            $table->string('wh_id', 100);
            $table->decimal('qty', 16, 2);
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
        Schema::dropIfExists('tbl_stock_opname_customer');
    }
};
