<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('telepon')->nullable()->after('address');
            $table->foreignId('provinsi_id')->nullable()->after('telepon');
            $table->foreignId('kabupaten_id')->nullable()->after('provinsi_id');
            $table->foreignId('kecamatan_id')->nullable()->after('kabupaten_id');
            $table->foreignId('kelurahan_id')->nullable()->after('kecamatan_id');
            $table->char('kodepos', 6)->nullable()->after('kelurahan_id');
            $table->string('latitude')->nullable()->after('kodepos');
            $table->string('longitude')->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('telepon');
            $table->dropColumn('provinsi_id');
            $table->dropColumn('kabupaten_id');
            $table->dropColumn('kecamatan_id');
            $table->dropColumn('kelurahan_id');
            $table->dropColumn('kodepos');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }
}
