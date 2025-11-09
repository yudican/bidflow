<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('vid')->nullable();
            $table->string('voucher_code')->nullable();
            $table->string('title')->nullable();
            $table->float('nominal')->nullable();
            $table->float('percentage')->nullable();
            $table->float('validity_period')->nullable();
            $table->string('total')->nullable();
            $table->text('description')->nullable();
            $table->string('slug')->nullable();
            $table->char('status', 1)->default(1)->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
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
        Schema::dropIfExists('vouchers');
    }
}
