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
                'variable_type' => 'resource_type'
            ],
            [
                'variable_type' => 'project_status'
            ],
            [
                'variable_type' => 'tag'
            ],
            [
                'variable_type' => 'task_status'
            ],
            [
                'variable_type' => 'task_type'
            ],
            [
                'variable_type' => 'task_action_type'
            ],
            [
                'variable_type' => 'issue_status'
            ],
            [
                'variable_type' => 'working_days'
            ]

        ]);

        $variables = DbVariables::all();
        foreach ($variables as $variable) {
            switch ($variable->id) {
                    //resource status
                case 1:
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

                    //resource type
                case 2:
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

                    //project status
                case 3:
                    DbVariablesDetail::insert(
                        [
                            [
                                "variable_id" => $variable->id,
                                "value" => "pending"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "completed"
                            ]
                        ]
                    );
                    break;

                    //tag
                case 4:
                    DbVariablesDetail::insert(
                        [
                            [
                                "variable_id" => $variable->id,
                                "value" => "developer"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "approver"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "QA"
                            ]
                        ]
                    );
                    break;

                    //task status
                case 5:
                    DbVariablesDetail::insert(
                        [
                            [
                                "variable_id" => $variable->id,
                                "value" => "notAssign"
                            ],

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
                                "value" => "issue"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "inReview"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "completed"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "approve"
                            ]
                        ]
                    );
                    break;

                    //task type
                case 6:
                    DbVariablesDetail::insert(
                        [
                            [
                                "variable_id" => $variable->id,
                                "value" => "individual"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "project"
                            ]
                        ]
                    );
                    break;

                    //task action
                case 7:
                    DbVariablesDetail::insert(
                        [
                            [
                                "variable_id" => $variable->id,
                                "value" => "pause"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "resume"
                            ]
                        ]
                    );
                    break;

                    //issue status
                case 8:
                    DbVariablesDetail::insert(
                        [
                            [
                                "variable_id" => $variable->id,
                                "value" => "pending"
                            ],
                            [
                                "variable_id" => $variable->id,
                                "value" => "resolved"
                            ]
                        ]
                    );
                    break;
                    case 9:
                        DbVariablesDetail::insert(
                            [
                                [
                                    "variable_id" => $variable->id,
                                    "value" =>"22"
                                ]
                            ]
                        );
                        break;
            }
        }
    }
}
