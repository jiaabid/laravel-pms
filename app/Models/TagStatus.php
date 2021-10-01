<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TagStatus extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'tag_id',
        'task_status_id',
        'updated_at',
        'created_at'
    ];
}
