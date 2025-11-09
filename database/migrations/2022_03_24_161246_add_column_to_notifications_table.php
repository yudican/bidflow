<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('type')->nullable()->after('user_id');
            $table->string('other_id')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('other_id');
        });
    }
}
