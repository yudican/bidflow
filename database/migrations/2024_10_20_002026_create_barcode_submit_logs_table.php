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
        Schema::create('barcode_submit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('po_id')->nullable();
            $table->string('activity')->nullable();
            $table->string('hit_date')->nullable();
            $table->string('hit_user')->nullable();
            $table->string('status')->nullable();
            $table->string('description')->nullable();
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
        Schema::dropIfExists('barcode_submit_logs');
    }
};
