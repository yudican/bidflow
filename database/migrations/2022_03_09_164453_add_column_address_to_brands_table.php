<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnAddressToBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->unsignedBigInteger('provinsi_id')->after('address')->nullable();
            $table->unsignedBigInteger('kabupaten_id')->after('provinsi_id')->nullable();
            $table->unsignedBigInteger('kecamatan_id')->after('kabupaten_id')->nullable();
            $table->unsignedBigInteger('kelurahan_id')->after('kecamatan_id')->nullable();
            $table->unsignedBigInteger('kodepos')->after('kelurahan_id')->nullable();
            $table->string('origin_code')->after('kodepos')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('provinsi_id');
            $table->dropColumn('kabupaten_id');
            $table->dropColumn('kecamatan_id');
            $table->dropColumn('kelurahan_id');
            $table->dropColumn('kodepos');
            $table->dropColumn('origin_code');
        });
    }
}
