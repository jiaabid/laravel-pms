<?php

namespace Database\Seeders;

use App\Models\DbVariables;
use App\Models\DbVariablesDetail;
use Illuminate\Database\Seeder;

class DbVariableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DbVariables::insert([
            [
                'variable_type' => 'resource_status'
            ],
            [
                'variable_type' => 'task_status'
            ],
            [
                'variable_type' => 'project_status'
            ],
            [
                'variable_type' => 'tag'
            ],
            [
                'variable_type' => 'resource_type'
            ]
        ]);

        $variables = DbVariables::all();
        foreach ($variables as $variable) {
            switch ($variable->variable_type) {
                case 'resource_status':
                    DbVariablesDetail::insert(
                        [
                            [
                                "variable_id" => $variable->id,
                                "value" => "busy"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "free"
                            ]
                        ]
                    );
                    break;
                case 'resource_type':
                    DbVariablesDetail::insert(
                        [
                            [
                                "variable_id" => $variable->id,
                                "value" => "human"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "non-human"
                            ]
                        ]
                    );
                    break;
                case 'task_status':
                    DbVariablesDetail::insert(
                        [
                            [
                                "variable_id" => $variable->id,
                                "value" => "pending"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "inProgress"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "completed"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "notAssign"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "bug"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "inReview"
                            ]
                        ]
                    );
                    break;
                case 'project_status':
                    DbVariablesDetail::insert(
                        [
                            [
                                "variable_id" => $variable->id,
                                "value" => "pending"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "completed"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "late"
                            ]
                        ]
                    );
                    break;
                case 'tag':
                    DbVariablesDetail::insert(
                        [
                            [
                                "variable_id" => $variable->id,
                                "value" => "backend developer"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "frontend developer"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "QA"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "team lead"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "project manager"
                            ]
                        ]
                    );
                    break;
            }
        }
    }
}
