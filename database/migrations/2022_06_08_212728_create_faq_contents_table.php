<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaqContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faq_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submenu_id');
            $table->foreignId('category_id');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('faq_contents');
    }
}
