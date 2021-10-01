<?php

namespace Database\Seeders;

use App\Models\DbVariables;
use App\Models\DbVariablesDetail;
use App\Models\TagStatus;
use Illuminate\Database\Seeder;

class TagStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $tagStatusId = DbVariables::where('variable_type','tag')->first()->id;
        $statuses = DbVariablesDetail::where('variable_id',$tagStatusId)->get()->value;
        TagStatus::insert([
          
        ]);
    }
}
