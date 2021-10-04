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

    //relation with department
    public function department()
    {
        return $this->belongsTo(Department::class,'dept_id');
    }

    //relation with department
    public function project()
    {
        return $this->hasMany(Project::class,'created_by');
        // return $this->belongsTo(Project::class);
    }

    public function task(){
        return $this->belongsToMany(Task::class,'h_resources_tasks','resource_id','task_id')
        ->withPivot(['status','sequence','tag'])
        ->as('check');
        // return $this->belongsToMany(Task::class,'h_resources_tasks','task_id','resource_id');
    }
    public function assigned_task(){
        return $this->belongsToMany(Task::class,'h_resources_tasks','resource_id','task_id')
        ->withPivot(['status','sequence','tag'])
        ->wherePivot('status','<>','notAssign');
        // return $this->belongsToMany(Task::class,'h_resources_tasks','task_id','resource_id');
    }

    public function my_task(){
        return 
    }
}
