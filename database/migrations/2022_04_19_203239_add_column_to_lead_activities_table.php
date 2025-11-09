<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToLeadActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('description');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('result')->nullable()->after('end_date');
            $table->text('attachment')->nullable()->after('result');
            $table->string('status')->nullable()->after('attachment');
            $table->foreignUuid('user_created')->nullable()->after('status');
            $table->foreignUuid('user_updated')->nullable()->after('user_created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_activities', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('result');
            $table->dropColumn('attachment');
            $table->dropColumn('status');
            $table->dropColumn('user_created');
            $table->dropColumn('user_updated');
        });
    }
}
