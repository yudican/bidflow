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
        Schema::create('order_submit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('submited_by')->nullable();
            $table->foreignUuid('updated_by')->nullable();
            $table->string('type_si')->nullable();
            $table->integer('total_failed')->nullable()->default(0);
            $table->integer('total_success')->nullable()->default(0);
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
        Schema::dropIfExists('order_submit_logs');
    }
};
