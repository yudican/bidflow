<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockCalcJobsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_calc_jobs', function (Blueprint $table) {
            $table->uuid('job_id')->primary();
            $table->string('status')->default('pending'); // pending, processing, done, failed
            $table->integer('progress')->default(0); // 0 - 100
            $table->integer('total_items')->nullable();
            $table->longText('result')->nullable(); // optional summary or link
            $table->text('meta')->nullable(); // optional payload (json)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_calc_jobs');
    }
}
