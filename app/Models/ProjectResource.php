<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectResource extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_resources';
    protected $fillable = [
        'project_id',
        'resource_id',
        'type'
    ];
}
