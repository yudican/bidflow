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
        Schema::create('order_shippings', function (Blueprint $table) {
            $table->id();
            $table->string('uid_lead')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('resi')->nullable();
            $table->string('expedition_name')->nullable();
            $table->enum('order_type', ['lead', 'manual', 'sales-return'])->nullable();
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
        Schema::dropIfExists('order_shippings');
    }
};
