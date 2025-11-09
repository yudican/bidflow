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
        Schema::create('prospect_activities', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('prospect_id');
            $table->text('notes');
            $table->string('attachment')->nullable();
            $table->date('submit_date')->nullable();
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
        Schema::dropIfExists('prospect_activities');
    }
};
