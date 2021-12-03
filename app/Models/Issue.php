<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Issue extends Model
{
    use HasFactory, SoftDeletes;
       
    /**
     * initially set pending status in the object
     *
     * @return void
     */
    public function __construct()
    {
        $id = DbVariables::where('variable_type', 'issue_status')->first()->id;

        $value = DbVariablesDetail::where('variable_id', $id)
            ->where('value', 'pending')->first()->id;
      
        $this->status = $value; //or fetch from db.
    }

    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'task_id',
        'resource_id',
        'status',
        'approved'
    ];
    
    /**
     * table
     *
     * @var string
     */
    protected $table = "issues";

    //relations
    
    /**
     * task
     *
     * @return void
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
