<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DbVariablesDetail extends Model
{
    use HasFactory, SoftDeletes;

    //Relations

    public function variable()
    {
        return $this->belongsTo(DbVariables::class, 'variable_id');
    }

    public function tag_status(){
        return $this->hasMany(TagStatus::class,'status_id');
    }
    //Queries  

    /**
     * retrieving the Dbvariable type object with type name
     *
     * @param  mixed $query
     * @param  string $type
     * @return DbVariable object
     */
    public function scopeVariableType($query, string $type)
    {
        $query->whereHas('variable', function ($inner_query) use ($type) {
            return $inner_query->where('variable_type', $type);
        });
    }


    /**
     * retrieving the DbVariableDetail object by passing the value
     *
     * @param  mixed $query
     * @param  string $value
     * @return DbVariableDetail object
     */
    public function scopeVariableValue($query, string $value)
    {
        return $query->where('value', $value);
    }

    /**
     * retrieving the DbVariableDetail object by passing the id
     *
     * @param  mixed $query
     * @param  int $id
     * @return DbVariableDetail object
     */
    public function scopeStatusById($query, int $id)
    {
        // dd($id);
        return $query->where('id', $id);
    }
}
