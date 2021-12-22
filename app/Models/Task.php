<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Tag;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    
    /**
     * initialy assign the pending status to the Task object
     *
     * @return void
     */
    public function __construct()
    {
        $id = DbVariables::where('variable_type', 'task_status')->first()->id;

        $value = DbVariablesDetail::where('variable_id', $id)
            ->where('value', 'pending')->first()->id;
        // dd($value);
        $this->status = $value; //or fetch from db.
    }

    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'decription',
        'start_date',
        'end_date',
        'status',
        'project_id',
        'created_by',
        'updated_by',
        'updated_at',
        'created_at'

    ]; 
    
    
    /**
     * table
     *
     * @var string
     */
    protected $table = 'tasks';
    
    //relations
    
    /**
     * team
     *
     * @return void
     */
    public function team()
    {
        return $this->belongsToMany(User::class, 'resources_tasks', 'task_id', 'resource_id')
        ->using(TagDetail::class)
        ->withPivot('tag_id')
        ->withPivot(['id','status', 'sequence',  'estimated_effort', 'total_effort', 'delay','start_date','end_date','tag_id','start_at','pause'])
        ->wherePivot('deleted_at',null)
        ->as('detail');
            // ->withPivot(['status', 'sequence', 'tag', 'estimated_effort', 'total_effort', 'delay','start_date','end_date'])
            // ->with('.tagStatus');
            // ->as('resourceDetail');
            // ->with('resourceDetail');
    }
   


    
    /**
     * project
     *
     * @return void
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

        
    /**
     * issues
     *
     * @return void
     */
    public function issues()
    {
        return $this->hasMany(Issue::class)->where('issues.deleted_at',null);
    }
   
}
