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
            $table->string('is_po_created')->nullable();
            $table->string('purchase_order_id')->nullable();
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
            $table->dropColumn('is_po_created');
            $table->dropColumn('purchase_order_id');
        });
    }
};
