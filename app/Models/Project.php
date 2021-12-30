<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * initially assign pending status to the object
     *
     * @return void
     */
    public function __construct()
    {
        $id = DbVariables::where('variable_type', 'project_status')->first()->id;
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
        'dept_id',
        'status',
        'created_by',
        'updated_by',
        'start_date',
        'end_date'
    ];
    
    /**
     * table
     *
     * @var string
     */
    protected $table = "projects";



    //relations
    
   
    /**
     * department
     *
     * @return void
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
        // return $this->hasOne(Department::class);
    }

      
    /**
     * user
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
        // return $this->hasOne(User::class);
    }

   
    /**
     * relation with doc via bridge table project_docs table
     *
     * @return void
     */
    public function doc()
    {
        return $this->belongsToMany(Doc::class, 'project_docs', 'project_id', 'doc_id')->wherePivot('deleted_at',null);
    }

        
    /**
     * relation with users via bridge table project_resources table
     *
     * @return void
     */
    public function human_resource(){
        return $this->belongsToMany(User::class,'project_resources','project_id','resource_id')->wherePivot('type',DbVariablesDetail::variableType('resource_type')->variableValue('human')->first()->id)->wherePivot('deleted_at',NULL);
    }

        
    /**
     * relation with non-man resources via bridge table project_resources table
     *
     * @return void
     */
    public function nonhuman_resource(){
        return $this->belongsToMany(NonHumanResources::class,'project_resources','project_id','resource_id')->wherePivot('type',DbVariablesDetail::variableType('resource_type')->variableValue('non-human')->first()->id)->wherePivot('deleted_at',NULL);
    }

        
    /**
     * tasks
     *
     * @return void
     */
    public function tasks(){
        return $this->hasMany(Task::class)->where('deleted_at',null);
    }
    
    /**
     * creator
     *
     * @return void
     */
    public function creator(){
        return $this->belongsTo(User::class,'created_by','id');
    }
}
