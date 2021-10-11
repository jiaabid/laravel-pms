<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roles extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "roles";
    public function children()
    {
        return $this->hasMany(self::class, 'parent');
    }
}
