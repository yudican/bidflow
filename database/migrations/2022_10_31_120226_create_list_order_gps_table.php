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
        Schema::create('list_order_gp', function (Blueprint $table) {
            $table->id();
            $table->dateTime('create_date')->default(now());
            $table->foreignUuid('submit_by')->constrained('users');
            $table->string('status')->default('failed'); // success, failed
            $table->integer('total_success')->default(0);
            $table->integer('total_failed')->default(0);
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
        Schema::dropIfExists('list_order_gp');
    }
};
