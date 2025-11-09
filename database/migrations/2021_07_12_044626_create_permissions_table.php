<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('permission_value', 191);
            $table->string('permission_name', 191);
            $table->timestamps();
        });

        DB::table('permissions')->insert([
            [
                'id' => '7c0dcd38-abf2-4182-8ea2-f23831188f91',
                'permission_value' => 'dashboard:read',
                'permission_name' => 'Access dashboard menu',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => '1ea292cd-3273-40dc-a031-5d3aadb3ca5e',
                'permission_value' => 'menu:read',
                'permission_name' => 'Access menu',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => '08a7f9bb-eb21-4bfe-90f1-a19291ecea67',
                'permission_value' => 'menu:update',
                'permission_name' => 'Update menu',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'c16bae28-61c9-49de-92fe-409d58d46a90',
                'permission_value' => 'menu:create',
                'permission_name' => 'Create menu',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => '3ead74e6-8f7c-4594-a198-9e6f9f27a174',
                'permission_value' => 'menu:delete',
                'permission_name' => 'Delete menu',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => '77e7b7fd-90db-41a0-9925-b28356455b1d',
                'permission_value' => 'role:read',
                'permission_name' => 'Access role',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'bddc0db5-0844-461f-b5a8-4872487a55b8',
                'permission_value' => 'role:update',
                'permission_name' => 'Update role',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => '5e68ef0b-99eb-43aa-bccc-6eda27f35d5b',
                'permission_value' => 'role:create',
                'permission_name' => 'Create role',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => '5d410b63-66c4-4081-8503-aff7b125d84b',
                'permission_value' => 'role:delete',
                'permission_name' => 'Delete role',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'abc26750-899c-4ebb-b6f2-5d2426a2d1c2',
                'permission_value' => 'permission:read',
                'permission_name' => 'Access permission',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => '2a049bdd-4111-4522-a973-02188de661a4',
                'permission_value' => 'permission:update',
                'permission_name' => 'Update permission',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'ec2c5f16-0a94-4c15-a27c-26215437e80a',
                'permission_value' => 'permission:create',
                'permission_name' => 'Create permission',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'id' => 'a8a923bf-7f6c-4030-a162-13cecd56a806',
                'permission_value' => 'permission:delete',
                'permission_name' => 'Delete permission',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}
