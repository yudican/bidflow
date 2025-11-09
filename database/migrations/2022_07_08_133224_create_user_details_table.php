<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id');
            // social media
            $table->string('instagram_url')->nullable();
            $table->string('shopee_url')->nullable();
            $table->string('tokopedia_url')->nullable();
            $table->string('bukalapak_url')->nullable();
            $table->string('lazada_url')->nullable();
            $table->string('other_url')->nullable();


            // foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('user_details');
    }
}
