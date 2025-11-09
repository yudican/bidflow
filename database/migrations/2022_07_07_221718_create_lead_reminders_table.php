<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('contact')->nullable();
            $table->boolean('before_7_day')->nullable();
            $table->boolean('before_3_day')->nullable();
            $table->boolean('before_1_day')->nullable();
            $table->boolean('after_7_day')->nullable();
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
        Schema::dropIfExists('lead_reminders');
    }
}
