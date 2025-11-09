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
    Schema::connection('pgsql')->create('accurate_actual_stocks', function (Blueprint $table) {
      $table->id();
      $table->string('count_id')->nullable();
      $table->date('date')->nullable();
      $table->bigInteger('customer_id')->nullable();
      $table->string('product_code')->nullable();
      $table->string('actual_stock')->nullable();
      $table->string('pic_name')->nullable();
      $table->text('notes')->nullable();
      $table->string('key')->nullable();
      $table->string('upload_by')->nullable();
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
    Schema::connection('pgsql')->dropIfExists('accurate_actual_stocks');
  }
};
