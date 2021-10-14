<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doc extends Model
{
    use HasFactory,SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'link'
    ];
    
    /**
     * table
     *
     * @var string
     */
    protected $table = 'docs';

    //relations

        
    /**
     * project
     *
     * @return void
     */
    public function project(){
        return $this->belongsToMany(Project::class,'project_docs');
    }
}
