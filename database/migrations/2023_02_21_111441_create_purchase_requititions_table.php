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
        Schema::create('purchase_requititions', function (Blueprint $table) {
            $table->id();
            $table->string('uid_requitition')->unique();
            $table->foreignId('brand_id')->constrained('brands');
            $table->string('project_name')->nullable();
            $table->string('request_by_name')->nullable();
            $table->string('request_by_email')->nullable();
            $table->string('request_by_division')->nullable();
            $table->date('request_date')->nullable();
            $table->text('request_note')->nullable();
            $table->char('request_status', 1)->nullable()->default(0);
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
        Schema::dropIfExists('purchase_requititions');
    }
};
