<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;

class UserAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
        // $route_name = $request->route()->getName();
        // $user = auth()->user();
        // $menus = $user->menus;
        // foreach ($menus as $menu) {
        //     if ($menu->menu_route == $route_name) {
        //         return $next($request);
        //     }
        //     foreach ($menu->children as $children) {
        //         foreach ($menu->children()->whereIn('id', $user->menu_id)->get() as $children) {
        //             if ($children->menu_route == $route_name) {
        //                 return $next($request);
        //             }
        //         }
        //     }
        // }
        // return abort(403);
    }
}
