<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToProductImportTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_import_temps', function (Blueprint $table) {
            $table->enum('status_convert', ['failed', 'success'])->default('failed')->after('status_import');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_import_temps', function (Blueprint $table) {
            $table->dropColumn('status_convert');
        });
    }
}
