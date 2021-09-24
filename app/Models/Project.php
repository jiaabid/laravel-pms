<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'dept_id',
        'created_by',
        'updated_by',
        'start_date',
        'end_date'
    ];

    protected $table = "projects";

    protected $casts = [
        'docs' => 'array'
    ];
    
    //relations
    //project has department
    public function department()
    {
        return $this->hasOne(Department::class);
    }

    //project has department
    public function user()
    {
        return $this->hasOne(User::class);
    }
}
