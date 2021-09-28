<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];
    protected $table = 'departments';


    //relations
    //user has department id
    public function user()
    {
        return $this->hasMany(User::class,'dept_id');
    }

    public function project()
    {
        return $this->hasOne(Project::class);
    }
}
