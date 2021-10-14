<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonHumanResources extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    /**
     * table
     *
     * @var string
     */
    protected $table = 'non_human_resources';

    //relations

    /**
     * projects
     *
     * @return void
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_resources', 'resource_id', 'project_id');
    }
}
