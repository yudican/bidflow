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
        Schema::connection('pgsql')->create('accurate_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accurate_id')->unique();
            $table->string('name')->nullable();
            $table->string('item_no')->nullable();              // dari "no"
            $table->string('unit1')->nullable();                // dari "unit1"
            $table->string('unit1_name')->nullable();           // dari "unit1Name"
            $table->string('item_type_name')->nullable();       // dari "itemTypeName"
            $table->string('item_category')->nullable();        // dari "itemCategory.name"
            $table->json('item_category_full')->nullable();     // jika ingin simpan seluruh itemCategory JSON
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
        Schema::connection('pgsql')->dropIfExists('accurate_items');
    }
};
