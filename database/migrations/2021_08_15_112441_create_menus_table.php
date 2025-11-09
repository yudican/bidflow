<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('menu_label', 50);
            $table->string('menu_route', 30);
            $table->string('menu_icon', 30)->nullable();
            $table->integer('menu_order')->default(0)->nullable();
            $table->char('show_menu', 1)->default(1)->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->timestamps();
        });

        $menus = [
            [
                'menu_label' => 'Dashboard',
                'menu_route' => 'dashboard',
                'menu_icon' => 'fas fa-home',
                'menu_order' => 1,
                'show_menu' => 1,
                'parent_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'menu_label' => 'Site Management',
                'menu_route' => '#',
                'menu_icon' => 'fas fa-key',
                'menu_order' => 2,
                'show_menu' => 1,
                'parent_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'menu_label' => 'Menu',
                'menu_route' => 'menu',
                'menu_icon' => null,
                'menu_order' => 1,
                'show_menu' => 1,
                'parent_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'menu_label' => 'Role',
                'menu_route' => 'role',
                'menu_icon' => null,
                'menu_order' => 2,
                'show_menu' => 1,
                'parent_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'menu_label' => 'Permission',
                'menu_route' => 'permission',
                'menu_icon' => null,
                'menu_order' => 3,
                'show_menu' => 1,
                'parent_id' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'menu_label' => 'Crud Generator',
                'menu_route' => 'crud.generator',
                'menu_icon' => 'fas fa-cogs',
                'menu_order' => 3,
                'show_menu' => 1,
                'parent_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'menu_label' => 'Role Permission',
                'menu_route' => 'permission.role',
                'menu_icon' => 'fas fa-cogs',
                'menu_order' => 4,
                'show_menu' => 0,
                'parent_id' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        DB::table('menus')->insert($menus);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menus');
    }
}
