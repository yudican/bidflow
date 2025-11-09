<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('bid')->nullable();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('brand_id')->nullable();
            $table->char('status', 1)->default(1)->nullable();
            $table->foreignUuid('user_id')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
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
        Schema::dropIfExists('banners');
    }
}
