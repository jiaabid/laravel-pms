<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DbVariables extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'variable_type'
    ];

    protected $table = 'db_variables';

    public function detail(){
        return $this->hasMany(DbVariablesDetail::class,'variable_id','id');
    }
}
