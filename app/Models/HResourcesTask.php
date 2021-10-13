<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HResourcesTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'task_id',
        'resource_id',
        'sequence',
        'start_at',
        'end_at',
        'status',
        'estimated_effort',
        'total_effort',
        'start_date',
        'end_date',
        'pause',
        'delay'
    ];
    protected $table = 'resources_tasks';
}
