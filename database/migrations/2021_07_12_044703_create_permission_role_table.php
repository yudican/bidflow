<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePermissionRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('permission_id');
            $table->foreignUuid('role_id');
            // $table->timestamps();

            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        DB::table('permission_role')->insert([
            [
                'permission_id' => '7c0dcd38-abf2-4182-8ea2-f23831188f91',
                'role_id' => '0feb7d3a-90c0-42b9-be3f-63757088cb9a',
            ],
            [
                'permission_id' => '7c0dcd38-abf2-4182-8ea2-f23831188f91',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => '1ea292cd-3273-40dc-a031-5d3aadb3ca5e',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => '08a7f9bb-eb21-4bfe-90f1-a19291ecea67',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => 'c16bae28-61c9-49de-92fe-409d58d46a90',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => '3ead74e6-8f7c-4594-a198-9e6f9f27a174',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => '77e7b7fd-90db-41a0-9925-b28356455b1d',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => 'bddc0db5-0844-461f-b5a8-4872487a55b8',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => '5e68ef0b-99eb-43aa-bccc-6eda27f35d5b',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => '5d410b63-66c4-4081-8503-aff7b125d84b',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => 'abc26750-899c-4ebb-b6f2-5d2426a2d1c2',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => '2a049bdd-4111-4522-a973-02188de661a4',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => 'ec2c5f16-0a94-4c15-a27c-26215437e80a',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
            ],
            [
                'permission_id' => 'a8a923bf-7f6c-4030-a162-13cecd56a806',
                'role_id' => '0c1afb3f-1de0-4cb4-a512-f8ef9fc8e816',
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
        Schema::dropIfExists('permission_role');
    }
}
