<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnHidenameToCommentRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comment_ratings', function (Blueprint $table) {
            $table->char('hide_name', 1)->nullable()->default(0)->after('comment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comment_ratings', function (Blueprint $table) {
            $table->dropColumn('hide_name');
        });
    }
}
