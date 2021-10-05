<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\DB;

trait ResusableQueryTrait
{
    public function get_child_roles(User $user)
    {
        $id = $user->role_id;
        $roles = DB::select('call role_childs(?)', [$id]);
        $roles = collect($roles)->pluck('id');
        return $roles;
    }
}
