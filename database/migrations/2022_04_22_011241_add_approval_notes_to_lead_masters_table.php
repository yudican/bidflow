<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalNotesToLeadMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_masters', function (Blueprint $table) {
            $table->text('approval_notes')->nullable()->after('is_negotiation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_masters', function (Blueprint $table) {
            $table->dropColumn('approval_notes');
        });
    }
}
