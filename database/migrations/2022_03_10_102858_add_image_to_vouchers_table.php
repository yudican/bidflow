<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('image')->nullable()->after('status');
            $table->dateTime('start_date')->nullable()->after('image');
            $table->dateTime('end_date')->nullable()->after('start_date');
            $table->string('min')->default(0)->after('end_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('min');
        });
    }
}
