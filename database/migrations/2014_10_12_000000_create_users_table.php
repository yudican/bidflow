<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignId('current_team_id')->default(1)->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->timestamps();
        });

        DB::table('users')->insert([
            [
                'id' => '963b12db-5dbf-4cd5-91f7-366b2123ccb9',
                'name' => 'Superadmin',
                'username' => 'superadmin',
                'email' => 'superadmin@admin.com',
                'password' => Hash::make('superadmin123'),
                'current_team_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'e859c822-4bd1-472b-84eb-361799c0d850',
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('admin123'),
                'current_team_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'b831b338-7a64-4c5e-a613-b83ddd9b133d',
                'name' => 'Member',
                'username' => 'member',
                'email' => 'member@admin.com',
                'password' => Hash::make('member123'),
                'current_team_id' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
