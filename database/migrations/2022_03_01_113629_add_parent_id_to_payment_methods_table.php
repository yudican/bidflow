<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // add column parent_id and payment_type to payment_methods table after status column
            $table->unsignedBigInteger('parent_id')->nullable()->after('status');
            $table->string('payment_type')->nullable()->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // remove column parent_id and payment_type from payment_methods table
            $table->dropColumn('parent_id');
            $table->dropColumn('payment_type');
        });
    }
}
