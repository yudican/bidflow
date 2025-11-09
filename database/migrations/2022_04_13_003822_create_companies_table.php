<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('brand_id')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('owner_phone')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_phone')->nullable();
            $table->foreignUuid('user_id')->nullable();
            $table->char('status', 1)->nullable();
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
        Schema::dropIfExists('companies');
    }
}
