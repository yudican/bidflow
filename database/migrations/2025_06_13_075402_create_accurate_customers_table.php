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
        Schema::create('accurate_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accurate_id'); // ID dari Accurate
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('wp_name')->nullable();
            $table->json('default_warehouse')->nullable();
            $table->json('customer_receivable_account_list')->nullable();
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
