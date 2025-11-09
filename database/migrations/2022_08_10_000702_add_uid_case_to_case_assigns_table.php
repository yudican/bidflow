<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUidCaseToCaseAssignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('case_assigns', function (Blueprint $table) {
            $table->string('uid_case')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('case_assigns', function (Blueprint $table) {
            $table->dropColumn('uid_case');
        });
    }
}
