<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateTeamUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id');
            $table->foreignUuid('user_id');
            $table->string('role', 191);
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        DB::table('team_user')->insert(
            [
                [
                    'team_id' => 1,
                    'user_id' => 'e859c822-4bd1-472b-84eb-361799c0d850',
                    'role' => 'admin',
                ],
                [
                    'team_id' => 1,
                    'user_id' => 'b831b338-7a64-4c5e-a613-b83ddd9b133d',
                    'role' => 'member',
                ],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_user');
    }
}
