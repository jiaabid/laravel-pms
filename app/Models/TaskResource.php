<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskResource extends Model
{
    use HasFactory;
    protected $table='resources_tasks';

    public function tagStatus()
    {
        return $this->belongsToMany(TagStatus::class,'tag_id','tag');
    }
}
