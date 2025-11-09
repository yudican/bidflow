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
    Schema::connection('pgsql')->create('stock_count', function (Blueprint $table) {
      $table->id();
      $table->string('count_id')->nullable();
      $table->date('date')->nullable();
      $table->string('customer_id')->nullable();
      $table->string('product_code')->nullable();
      $table->string('actual_stock')->nullable();
      $table->string('pic_name')->nullable();
      $table->text('notes')->nullable();
      $table->string('key')->nullable();
      $table->string('created_by')->nullable();
      $table->timestamps();

      // Add indexes for better performance
      $table->index('count_id');
      $table->index('customer_id');
      $table->index('product_code');
      $table->index('date');
      $table->index('created_by');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('pgsql')->dropIfExists('stock_count');
  }
};
