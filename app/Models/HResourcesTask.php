<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HResourcesTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_id',
        'resource_id'
    ];
    protected $table = 'h_resources_tasks';
}
