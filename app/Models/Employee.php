<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "joining_date",
        "designation",
        "salary",
        "duty_start",
        "duty_end",
        "working_hrs",
        "break",
        "user_id"
    ];

    protected $table = "employees";
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
