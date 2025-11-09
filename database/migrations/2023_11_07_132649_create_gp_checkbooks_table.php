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
        Schema::create('gp_checkbooks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->nullable();
            $table->string('description')->nullable();
            $table->string('company_address')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('currency_id')->nullable();
            $table->string('status')->nullable();
            $table->char('gp_status', 1)->nullable()->default(0);
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
        Schema::dropIfExists('gp_checkbooks');
    }
};
