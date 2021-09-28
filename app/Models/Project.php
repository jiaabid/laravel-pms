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



    //relations
    //project has department
    public function department()
    {
        return $this->belongsTo(Department::class,'dept_id');
        // return $this->hasOne(Department::class);
    }

    //project has department
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
        // return $this->hasOne(User::class);
    }

    //docs related to the single project
    public function doc()
    {
        return $this->belongsToMany(Doc::class, 'project_docs', 'project_id', 'doc_id');
    }
}
