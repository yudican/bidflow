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
        Schema::create('purchase_log_approvals', function (Blueprint $table) {
            $table->id();
            $table->integer('purchase_requitition_id')->nullable();
            $table->string('approval_id')->nullable();
            $table->string('action')->nullable();
            $table->string('execute_by')->nullable();
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
        Schema::dropIfExists('purchase_log_approvals');
    }
};
