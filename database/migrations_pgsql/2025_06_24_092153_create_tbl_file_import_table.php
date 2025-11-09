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
        Schema::connection('pgsql')->create('tbl_file_import', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('type', 50);
            $table->string('nama_file');
            $table->string('loc_file'); 
            $table->unsignedBigInteger('upload_by')->nullable();
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
        Schema::dropIfExists('tbl_file_import');
    }
};
