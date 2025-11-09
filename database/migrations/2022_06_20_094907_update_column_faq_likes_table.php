<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnFaqLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('faq_likes', function (Blueprint $table) {
            $table->renameColumn('faq_sub_menu_id', 'content_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('faq_likes', function (Blueprint $table) {
            $table->renameColumn('content_id', 'faq_sub_menu_id');
        });
    }
}
