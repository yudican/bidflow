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
        Schema::create('accurate_item_transfer_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id'); // FK ke tabel transfer
            $table->unsignedBigInteger('accurate_detail_id')->nullable(); // API: id

            // Info Item
            $table->string('transfer_no')->nullable();
            $table->string('item_no')->nullable();
            $table->string('item_name')->nullable();
            $table->string('item_type')->nullable();
            $table->string('item_code')->nullable();

            $table->string('unit_name')->nullable();
            $table->decimal('quantity', 20, 6)->default(0);
            $table->string('status_name')->nullable();
            $table->string('charField3')->nullable();
            $table->string('created_by')->nullable();
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
        Schema::dropIfExists('accurate_item_transfer_details');
    }
};
