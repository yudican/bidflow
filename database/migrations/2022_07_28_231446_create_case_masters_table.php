<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('case_masters', function (Blueprint $table) {
            $table->id();
            $table->string('uid_case')->nullable();
            $table->string('title')->nullable();
            $table->foreignUuid('contact')->nullable();
            $table->foreignId('type_id')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->foreignId('priority_id')->nullable();
            $table->foreignId('source_id')->nullable();
            $table->foreignId('status_id')->nullable();
            $table->text('description')->nullable();
            $table->foreignUuid('created_by')->nullable();
            $table->foreignUuid('updated_by')->nullable();
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
        Schema::dropIfExists('case_masters');
    }
}
