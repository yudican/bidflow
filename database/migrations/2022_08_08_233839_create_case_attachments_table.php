<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('case_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('file_attachment')->nullable();
            $table->foreignUuid('upload_by')->nullable();
            $table->date('upload_at')->nullable();
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
        Schema::dropIfExists('case_attachments');
    }
}
