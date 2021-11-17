<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roles extends Model
{
    use HasFactory,SoftDeletes;
    
    /**
     * table
     *
     * @var string
     */
    protected $table = "roles";

    //relations
        
    /**
     * children (self join)
     *
     * @return void
     */
    public function children()
    {
        return $this->hasOne(Roles::class, 'parent');
    }
    public function parent()
    {
        return $this->belongsTo(Roles::class, 'parent');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'role_id');
    }
}
