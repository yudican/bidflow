<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('role_type', 191);
            $table->string('role_name', 191);
            $table->timestamps();
        });

        DB::table('roles')->insert([
            [
                'id' => 'aaf5ab14-a1cd-46c9-9838-84188cd064b6',
                'role_type' => 'superadmin',
                'role_name' => 'Superadmin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
                'role_type' => 'admin',
                'role_name' => 'Admin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => '0feb7d3a-90c0-42b9-be3f-63757088cb9a',
                'role_type' => 'member',
                'role_name' => 'Member',
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
        Schema::dropIfExists('roles');
    }
}
