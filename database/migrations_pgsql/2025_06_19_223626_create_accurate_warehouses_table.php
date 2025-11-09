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
        Schema::connection('pgsql')->create('accurate_warehouses', function (Blueprint $table) {
            $table->id(); // Auto-increment Laravel ID
            $table->unsignedBigInteger('accurate_id')->unique(); // dari "id"
            $table->string('name')->nullable();           // "Gudang - Invoice Cancellation 2024"
            $table->text('description')->nullable();      // "Release Kembali Faktur 2024 - Rejected"
            $table->string('pic')->nullable();            // bisa NULL
            $table->boolean('scrap_warehouse')->default(false);
            $table->boolean('default_warehouse')->default(false);
            $table->boolean('suspended')->default(false);
            $table->unsignedBigInteger('location_id')->nullable(); // dari "locationId"
            $table->integer('opt_lock')->nullable();               // dari "optLock"
            $table->unsignedBigInteger('address_id')->nullable(); // dari address.id
            $table->string('address_name')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_province')->nullable();
            $table->string('address_country')->nullable();
            $table->string('address_zip_code')->nullable();
            $table->string('address_concat')->nullable();

            $table->json('address_full')->nullable();
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
        Schema::table('accurate_warehouses', function (Blueprint $table) {
            //
        });
    }
};
