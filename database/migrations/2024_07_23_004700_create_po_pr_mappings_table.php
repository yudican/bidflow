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
        Schema::create('po_pr_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_order_id')->nullable();
            $table->string('purchase_requitition_id')->nullable();
            $table->string('po_number')->nullable();
            $table->string('pr_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('po_pr_mappings');
    }
};
