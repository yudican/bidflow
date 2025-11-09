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
        Schema::create('stock_count', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->nullable();
            $table->string('created_by')->nullable();
            $table->text('description')->nullable();
            $table->integer('total_items')->default(0);
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
        Schema::dropIfExists('stock_count');
    }
};
