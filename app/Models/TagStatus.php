<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TagStatus extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'tag_id',
        'status_id'
    ];

    protected $table = "tag_statuses";

    //relations 
    
    /**
     * relation with DbVariableDetail 
     *
     * @return void
     */
    public function variable_detail(){
      return  $this->belongsTo(DbVariablesDetail::class,'status_id');
    }

    
}
