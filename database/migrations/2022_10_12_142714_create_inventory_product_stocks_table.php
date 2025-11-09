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
        Schema::create('inventory_product_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('uid_inventory', 15)->nullable();
            $table->string('reference_number')->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->foreignUuid('created_by')->nullable();
            $table->string('vendor')->nullable();
            $table->enum('status', ['draft', 'ready', 'done'])->nullable()->default('draft');
            $table->date('received_date')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('inventory_product_stocks');
    }
};
