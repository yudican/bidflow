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
        Schema::create('mp_order_list_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mp_order_list_item_id')->nullable();
            $table->string('status')->nullable();
            $table->string('message')->nullable();
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
        Schema::dropIfExists('m_p_order_list_import_logs');
    }
};
