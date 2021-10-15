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
        TagStatus::insert([

            [
                "tag_id" => 7,
                "status_id" => 12
            ],
            [
                "tag_id" => 7,
                "status_id" => 15
            ],
            [
                "tag_id" => 9,
                "status_id" => 12
            ], [
                "tag_id" => 9,
                "status_id" => 13
            ], [
                "tag_id" => 9,
                "status_id" => 15
            ],
            [
                "tag_id" => 8,
                "status_id" => 12
            ], [
                "tag_id" => 8,
                "status_id" => 13
            ], [
                "tag_id" => 8,
                "status_id" => 16
            ]
        ]);
    }
}
