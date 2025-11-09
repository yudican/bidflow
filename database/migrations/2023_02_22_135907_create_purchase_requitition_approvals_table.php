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
        Schema::create('purchase_requitition_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_requitition_id');
            $table->foreignUuid('user_id');
            $table->foreignUuid('role_id');
            $table->char('status', 1)->nullable()->default(0);
            $table->string('label')->nullable();
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
        Schema::dropIfExists('purchase_requitition_approvals');
    }
};
