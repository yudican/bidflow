<?php

namespace App\Jobs;

use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateTeamUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('role_type', '!=', 'member');
        })->get();

        foreach ($users as $key => $user) {
            $team = Team::updateOrCreate(['user_id' => $user->id,], [
                'name' => $user->name,
                'personal_team' => 1,
                'user_id' => $user->id,
            ]);
            $user->update(['current_team_id' => $team->id]);
        }
    }
}
