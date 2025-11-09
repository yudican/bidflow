<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateNotificationRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_template_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_template_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('role_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_notification_role');
    }
}
