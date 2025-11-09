<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandCustomerSupportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brand_customer_supports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('value');
            $table->char('status', 1)->default(0);
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
        Schema::dropIfExists('brand_customer_supports');
    }
}
