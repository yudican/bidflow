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
            $table->string('pr_number')->nullable()->after('uid_requitition');
            $table->string('vendor_code')->nullable()->after('pr_number');
            $table->string('vendor_name')->nullable()->after('vendor_code');
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms')->after('vendor_name');
            $table->foreignId('company_account_id')->nullable()->after('payment_term_id');
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
            $table->dropColumn('pr_number');
            $table->dropColumn('vendor_code');
            $table->dropColumn('vendor_name');
            $table->dropForeign(['payment_term_id']);
            $table->dropColumn('payment_term_id');
            $table->dropColumn('company_id');
        });
    }
};
