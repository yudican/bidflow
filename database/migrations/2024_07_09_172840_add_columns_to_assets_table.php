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
        Schema::table('assets', function (Blueprint $table) {
            $table->string('warranty')->nullable();
            $table->date('useful_life')->nullable();
            $table->text('asset_location')->nullable();
            $table->text('receiver_address')->nullable();
            $table->string('allocation_status')->nullable();
            $table->string('asset_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('warranty');
            $table->dropColumn('useful_life');
            $table->dropColumn('asset_location');
            $table->dropColumn('receiver_address');
            $table->dropColumn('allocation_status');
            $table->dropColumn('asset_number');
        });
    }
};
