<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToProductNeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_needs', function (Blueprint $table) {
            $table->integer('qty')->nullable()->after('product_id');
            $table->string('status')->nullable()->after('description');
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
        Schema::table('product_needs', function (Blueprint $table) {
            $table->dropColumn('qty');
            $table->dropColumn('status');
            $table->dropColumn('user_created');
            $table->dropColumn('user_updated');
        });
    }
}
