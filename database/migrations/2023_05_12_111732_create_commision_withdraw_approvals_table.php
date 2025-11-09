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
        Schema::create('commision_withdraw_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commision_withdraw_id');
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->char('status', 1)->default(0); // 0 = pending, 1 = approved, 2 = rejected
            $table->text('note')->nullable();
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
        Schema::dropIfExists('commision_withdraw_approvals');
    }
};
