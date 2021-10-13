<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Issue extends Model
{
    use HasFactory, SoftDeletes;
    public function __construct()
    {
        $id = DbVariables::where('variable_type', 'issue_status')->first()->id;

        $value = DbVariablesDetail::where('variable_id', $id)
            ->where('value', 'pending')->first()->id;
        // dd($value);
        $this->status = $value; //or fetch from db.
    }
    protected $fillable = [
        'name',
        'description',
        'task_id',
        'status',
        'approved'
    ];

    protected $table = "issues";

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
