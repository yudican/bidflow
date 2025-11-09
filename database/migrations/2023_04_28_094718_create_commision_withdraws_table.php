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
        Schema::create('commision_withdraws', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('request_by')->nullable();
            $table->foreignId('role_id')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('amount')->nullable();
            $table->enum('status', ['draft', 'waiting-approval', 'onprocess', 'success', 'canceled'])->default('draft');
            $table->string('nama_rekening')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_bank')->nullable();
            $table->string('notes')->nullable();
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
        Schema::dropIfExists('commision_withdraws');
    }
};
