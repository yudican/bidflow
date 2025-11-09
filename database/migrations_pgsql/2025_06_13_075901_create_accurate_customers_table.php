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
        Schema::connection('pgsql')->create('accurate_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accurate_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('wp_name')->nullable();
            $table->string('warehouse_name')->nullable();
            $table->json('default_warehouse')->nullable();
            $table->json('customer_receivable_account_list')->nullable();

            $table->string('category_name')->nullable(); // "CategoryName": "Corner"
            $table->string('customer_no')->nullable();   // "customerNo": "CR.0047"
            $table->string('npwp_no')->nullable();       // "npwpNo": "0013023841092000"
            $table->string('ship_street')->nullable();   // "shipStreet"
            $table->string('ship_province')->nullable(); // "shipProvince"
            $table->string('ship_city')->nullable();     // "shipCity"
            $table->string('work_phone')->nullable();    // "workPhone"
            $table->string('customer_receivable_name')->nullable(); // "customerReceivableName"

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
        Schema::dropIfExists('accurate_customers');
    }
};
