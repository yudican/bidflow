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
    Schema::connection('pgsql')->table('stock_count', function (Blueprint $table) {
      $table->text('attachments')->nullable()->after('notes');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('pgsql')->table('stock_count', function (Blueprint $table) {
      $table->dropColumn('attachments');
    });
  }
};
