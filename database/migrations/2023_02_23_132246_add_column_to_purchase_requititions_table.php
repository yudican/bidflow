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
        Schema::table('purchase_requititions', function (Blueprint $table) {
            $table->foreignUuid('received_by')->nullable()->after('company_account_id');
            $table->string('received_address')->nullable()->after('received_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_requititions', function (Blueprint $table) {
            $table->dropColumn('received_by');
            $table->dropColumn('received_address');
        });
    }
};
