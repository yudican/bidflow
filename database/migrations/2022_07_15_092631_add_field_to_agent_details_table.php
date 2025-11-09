<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToAgentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agent_details', function (Blueprint $table) {
            $table->char('agent_uid', 9)->nullable();
            $table->boolean('libur')->nullable()->default(false);
            $table->boolean('active')->nullable()->default(false);
            $table->text('whatsapp_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_details', function (Blueprint $table) {
            $table->dropColumn('libur');
            $table->dropColumn('active');
            $table->dropColumn('whatsapp_text');
        });
    }
}
