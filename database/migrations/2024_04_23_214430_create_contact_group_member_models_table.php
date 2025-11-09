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
        Schema::create('contact_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_group_id')->constrained('contact_groups')->onDelete('cascade');
            $table->foreignUuid('contact_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_admin')->default(false);
            $table->unique(['contact_group_id', 'contact_id']);
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
        Schema::dropIfExists('contact_group_members');
    }
};
