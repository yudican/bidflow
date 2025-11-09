<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusApprovalToCaseMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('case_masters', function (Blueprint $table) {
            $table->string('status_approval')->nullable();
            $table->string('approval_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('case_masters', function (Blueprint $table) {
            //
        });
    }
}
