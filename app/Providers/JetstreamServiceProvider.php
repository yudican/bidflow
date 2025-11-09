<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use App\Models\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePermissions();

        Jetstream::deleteUsersUsing(DeleteUser::class);
    }

    /**
     * Configure the permissions that are available within the application.
     *
     * @return void
     */
    protected function configurePermissions()
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        if (Schema::hasTable('roles')) {
            $roles = Role::with('permissions')->get();

            if (count($roles) > 0) {
                foreach ($roles as $role) {
                    $permissions = [];
                    if ($role->permissions->count() > 0) {
                        foreach ($role->permissions as $permission) {
                            $permissions[] = $permission->permission_value;
                        }
                    }
                    // dd($permissions);
                    Jetstream::role($role->role_type, __($role->role_name), $permissions)->description(__($role->role_name));
                }
            }
        }
    }
}
