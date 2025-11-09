<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telepon')->nullable()->after('profile_photo_path');
            $table->foreignId('brand_id')->nullable()->after('telepon');
            $table->string('google_id')->nullable()->after('brand_id');
            $table->string('facebook_id')->nullable()->after('google_id');
            $table->string('device_id')->nullable()->after('facebook_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('telepon');
            $table->dropColumn('brand_id');
            $table->dropColumn('google_id');
            $table->dropColumn('facebook_id');
            $table->dropColumn('device_id');
        });
    }
}
