<?php
namespace App\Models;
use App\Models\TagStatus;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TagDetail extends Pivot
{

    protected $table = "resources_tasks";

    //relations 
    
    public function taskId()
    {
        return $this->belongsTo(Task::class,'task_id');
    }
    
     
    public function tagId()
    {
        return $this->hasMany(TagStatus::class,'tag_id','tag_id');
    }
}
