<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_masters', function (Blueprint $table) {
            $table->id();
            $table->string('uid_lead')->nullable();
            $table->string('title')->nullable();
            $table->foreignUuid('contact')->nullable();
            $table->foreignUuid('sales')->nullable();
            $table->string('lead_type')->nullable();
            $table->foreignUuid('user_created')->nullable();
            $table->foreignUuid('user_updated')->nullable();
            $table->string('status')->nullable();
            $table->string('is_negotiation')->nullable();
            $table->string('status_negotiation')->nullable();
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
        Schema::dropIfExists('lead_masters');
    }
}
