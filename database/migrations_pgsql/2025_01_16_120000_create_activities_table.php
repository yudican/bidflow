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
        Schema::connection('pgsql')->create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_type')->default('other'); // Using string instead of enum for PostgreSQL
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_id')->nullable();
            $table->string('reference_id')->nullable(); // ID dari data yang terkait
            $table->string('reference_type')->nullable(); // tipe data yang dirujuk
            $table->json('metadata')->nullable(); // data tambahan dalam format JSON
            $table->timestamps();

            // Add indexes for better performance
            $table->index('activity_type');
            $table->index('user_id');
            $table->index('reference_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('pgsql')->dropIfExists('activities');
    }
};
