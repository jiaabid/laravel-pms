<?php

namespace App\Http\Traits;

use App\Models\User;
use Illuminate\Support\Facades\DB;

trait ReusableTrait
{
    public function get_child_roles(object $user)
    {
        $id = $user->role_id;
        $roles = DB::select('call role_childs(?)', [$id]);
        $roles = collect($roles)->pluck('id');
        // dd($roles);
        return $roles;

    }
}
