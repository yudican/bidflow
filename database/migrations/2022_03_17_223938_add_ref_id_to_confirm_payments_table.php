<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefIdToConfirmPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('confirm_payments', function (Blueprint $table) {
            $table->string('ref_id')->nullable()->after('foto_struk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('confirm_payments', function (Blueprint $table) {
            $table->dropColumn('ref_id');
        });
    }
}
