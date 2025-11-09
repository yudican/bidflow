<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('notification_code')->nullable();
            $table->string('notification_title')->nullable();
            $table->string('notification_subtitle')->nullable();
            $table->text('notification_body')->nullable();
            $table->enum('notification_type', ['web', 'mobile'])->nullable()->default('web');
            $table->text('notification_note')->nullable();
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
        Schema::dropIfExists('notification_templates');
    }
}
