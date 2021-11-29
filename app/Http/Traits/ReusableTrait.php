<?php

namespace App\Http\Traits;

use App\Models\User;
use Illuminate\Support\Facades\DB;

trait ReusableTrait
{    
    /**
     * for retrieving the child roles
     *
     * @param  User $user
     * @return array roles
     */
    public function get_child_roles($user)
    {
        $id = $user->role_id;
        $roles = DB::select('call role_childs(?)', [$id]);
        $roles = collect($roles)->pluck('id');
        return $roles;

    }

    /**
     * for retrieving the child users
     *
     * @param  User $user
     * @return array users
     */
    public function get_child_users($user)
    {
        $id = $user->id;
        $users = DB::select('call user_childs(?)', [$id]);
        $users = collect($users)->pluck('id');
        return $users;

    }
}
