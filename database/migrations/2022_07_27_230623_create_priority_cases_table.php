<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriorityCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('priority_cases', function (Blueprint $table) {
            $table->id();
            $table->string('priority_name')->nullable();
            $table->string('priority_day')->nullable();
            $table->string('notes')->nullable();
            $table->string('created_by')->nullable();
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
        Schema::dropIfExists('priority_cases');
    }
}
