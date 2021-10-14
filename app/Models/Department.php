<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];
        
    /**
     * table
     *
     * @var string
     */
    protected $table = 'departments';


    //relations
       
    /**
     * user
     *
     * @return void
     */
    public function user()
    {
        return $this->hasMany(User::class,'dept_id');
    }
    
    /**
     * project
     *
     * @return void
     */
    public function project()
    {
        return $this->hasOne(Project::class);
    }
}
