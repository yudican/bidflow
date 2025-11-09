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
        Schema::create('contact_group_address_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_group_id')->constrained('contact_groups')->onDelete('cascade');
            $table->string('nama')->nullable();
            $table->string('telepon')->nullable();
            $table->string('alamat')->nullable();
            $table->string('provinsi_id', 15)->nullable();
            $table->string('kabupaten_id', 15)->nullable();
            $table->string('kelurahan_id', 15)->nullable();
            $table->string('kecamatan_id', 15)->nullable();
            $table->string('kodepos', 5)->nullable();
            $table->char('default', 1)->nullable()->default(0);
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
        Schema::dropIfExists('contact_group_address_members');
    }
};
