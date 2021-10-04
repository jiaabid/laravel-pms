<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DbVariablesDetail extends Model
{
    use HasFactory, SoftDeletes;

    public function variable()
    {
        return $this->belongsTo(DbVariables::class, 'variable_id');
    }

    //getting  variable type
    public function scopeId($query, string $type)
    {
        $query->whereHas('variable',function ($inner_query) use($type){
            return $inner_query->where('variable_type',$type);
        });
    }

    //getting particular status
    public function scopeStatus($query, string $status)
    {
        return $query->where('value', $status);
    }

    public function scopeStatusById($query, int $id){
        // dd($id);
        return $query->where('id', $id);

    }
    public function scopePending($query)
    {
    }
}
