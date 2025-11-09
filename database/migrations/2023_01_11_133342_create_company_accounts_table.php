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
        Schema::create('company_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_phone')->nullable();
            $table->string('account_email')->nullable();
            $table->string('account_address')->nullable();
            $table->foreignId('provinsi_id')->nullable();
            $table->foreignId('kabupaten_id')->nullable();
            $table->foreignId('kecamatan_id')->nullable();
            $table->foreignId('kelurahan_id')->nullable();
            $table->string('kodepos')->nullable();
            $table->string('account_logo')->nullable();
            $table->text('account_description')->nullable();
            $table->char('status', 1)->nullable()->default(1);
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
        Schema::dropIfExists('company_accounts');
    }
};
