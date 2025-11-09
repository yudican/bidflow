<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadBillingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_billings', function (Blueprint $table) {
            $table->id();
            $table->string('uid_lead')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_bank')->nullable();
            $table->float('total_transfer')->nullable();
            $table->date('transfer_date')->nullable();
            $table->string('upload_billing_photo')->nullable();
            $table->string('upload_transfer_photo')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('lead_billings');
    }
}
