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
        Schema::create('accurate_customer_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accurate_id');
            $table->string('action', 10); // 'insert' atau 'update'
            $table->timestamp('created_at')->useCurrent();

            $table->index('accurate_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accurate_customer_logs');
    }
};
