<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('slug');
            $table->string('banner')->nullable()->after('logo');
            $table->string('phone')->nullable()->after('banner');
            $table->string('email')->nullable()->after('phone');
            $table->string('address')->nullable()->after('email');
            $table->string('twitter')->nullable()->after('address');
            $table->string('facebook')->nullable()->after('twitter');
            $table->string('instagram')->nullable()->after('facebook');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('logo');
            $table->dropColumn('banner');
            $table->dropColumn('phone');
            $table->dropColumn('email');
            $table->dropColumn('address');
            $table->dropColumn('twitter');
            $table->dropColumn('facebook');
            $table->dropColumn('instagram');
        });
    }
}
