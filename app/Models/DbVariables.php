<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DbVariables extends Model
{
    use HasFactory,SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'variable_type'
    ];

       
    /**
     * table
     *
     * @var string
     */
    protected $table = 'db_variables';

    //relations
        
    /**
     * detail
     *
     * @return void
     */
    public function detail(){
        return $this->hasMany(DbVariablesDetail::class,'variable_id','id');
    }
}
