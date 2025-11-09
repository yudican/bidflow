<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderToAgentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agent_details', function (Blueprint $table) {
            $table->integer('parent_id')->default(0);
            $table->integer('order')->default(0);
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
            $table->dropColumn('order');
            $table->dropColumn('parent_id');
        });
    }
}
