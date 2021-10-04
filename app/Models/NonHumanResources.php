<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonHumanResources extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    protected $table = 'non_human_resources';

    public function projects(){
        return $this->belongsToMany(Project::class,'project_resources','resource_id','project_id');
    }
}
