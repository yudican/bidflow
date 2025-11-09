<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_datas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->nullable();
            $table->foreignId('level_id')->nullable();
            $table->string('class_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('short_name');
            $table->string('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('phone1')->nullable();
            $table->string('phone2')->nullable();
            $table->string('phone3')->nullable();
            $table->string('fax')->nullable();
            $table->string('npwp')->nullable();
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
        Schema::dropIfExists('user_datas');
    }
}
