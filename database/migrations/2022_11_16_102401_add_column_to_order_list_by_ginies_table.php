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
        Schema::table('order_list_by_genies', function (Blueprint $table) {
            $table->enum('status_submit', ['submited', 'notsubmited'])->after('status')->default('notsubmited');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_list_by_genies', function (Blueprint $table) {
            $table->dropColumn('status_submit');
        });
    }
};
