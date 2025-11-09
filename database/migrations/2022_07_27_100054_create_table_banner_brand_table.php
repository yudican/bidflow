<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableBannerBrandTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banner_brand', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banner_brand');
    }
}
