<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'dept_id',
        'status',
        'type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //relations

    /**
     * relation with department 
     *
     * @return void
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }


    /**
     * relation with project table , foreign key :created_by
     *
     * @return void
     */
    public function project()
    {
        return $this->hasMany(Project::class, 'created_by');
        // return $this->belongsTo(Project::class);
    }

    /**
     *relation with task
     *
     * @return void
     */
    public function task()
    {
        return $this->belongsToMany(Task::class, 'resources_tasks', 'resource_id', 'task_id')
            ->withPivot(['status', 'sequence', 'tag'])
            ->as('check');
        // return $this->belongsToMany(Task::class,'h_resources_tasks','task_id','resource_id');
    }

    /**
     *relation with task via resources_tasks table
     *
     * @return void
     */
    public function assigned_task()
    {
        $notAssignId = DbVariablesDetail::variableType('task_status')->value('notAssign')->first()->id;
        return $this->belongsToMany(Task::class, 'resources_tasks', 'resource_id', 'task_id')
            ->withPivot(['status', 'sequence', 'tag'])
            ->wherePivot('status', '<>', $notAssignId);
    }

    /**
     *relation with projects via bridge table project_resources
     *
     * @return void
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_resources', 'resource_id', 'project_id');
    }

    /**
     *relation with employee 
     *
     * @return void
     */
    public function detail()
    {
        return $this->hasOne(Employee::class, "user_id");
    }
}
