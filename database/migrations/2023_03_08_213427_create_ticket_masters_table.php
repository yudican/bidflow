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
        Schema::create('ticket_masters', function (Blueprint $table) {
            $table->id();
            $table->string('uid_ticket')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('agent_id')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('assign_date')->nullable();
            $table->string('tags')->nullable();
            $table->string('note')->nullable();
            $table->string('status_ticket')->nullable();
            $table->string('status_approve')->nullable();
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
        Schema::dropIfExists('ticket_masters');
    }
};
