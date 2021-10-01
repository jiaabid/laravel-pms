<?php

namespace App\Http\Controllers;

use App\Models\HResourcesTask;
use App\Models\NhResourcesTask;
use App\Models\NonHumanResources;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    private $responseBody;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        try {

            // $tasks = Task::with('team')->with('resources')->get();
            $tasks = Task::all();
            foreach ($tasks as $task) {
                $task->team;
                $task->resources;
            }
            if ($tasks) {
                return response()->json([
                    'payload' => $tasks
                ]);
            } else {
            }
        } catch (Exception $e) {
        }
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if (auth()->user()->can('create task')) {
                //    dd($request->input('start_date'));
                $this->validate($request, [
                    'name' => "required|min:3|string",
                    'project_id' => "required|numeric",
                    'start_date' => "required|date",
                    'end_date' => 'required|date',


                ]);

                // 'humanResources' => 'required|array',
                // 'nonhumanResources' => 'array',
                $task = new Task();
                // dd($task);
                $task['name'] = $request->name;
                $task['start_date'] = $request->start_date;
                $task['end_date'] = $request->end_date;
                $task['project_id'] = $request->project_id;
                $task['description'] = $request->description ? $request->description : null;
                $task['created_by'] = auth()->user()->id;
                DB::beginTransaction();
                $saved = $task->save();
                //   dd($task->id);
                $taskId = $task->id;
                if ($saved) {
                    //insert in human resource bridge tabel
                    // $this->responseBody = $this->human_resource_bridge_table($taskId, $request->humanResources);

                    // if ($this->responseBody["status"]) {

                    //     //if non human resources then insert in bridge table
                    //     if ($request->nonhumanResources) {
                    //         $this->responseBody = $this->nonhuman_resource_bridge_table($taskId, $request->nonhumanResources);
                    //         if ($this->responseBody["status"]) {
                    //             DB::commit();
                    //             return response()->json([
                    //                 'payload' => $task,
                    //                 'status' => true
                    //             ], 201);
                    //         } else {
                    //             return response()->json($this->responseBody, 400);
                    //         }
                    //     }
                    DB::commit();
                    return response()->json([
                        'payload' => $task,
                        'status' => true
                    ], 201);
                    // } else {
                    //     return response()->json($this->responseBody, 400);
                    // }

                    // DB::commit();
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => "Error in saving"
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'payload' => "Unauthorized!"
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    function human_resource_bridge_table($taskId, $users)
    {
        // dd($taskId);
        $payload = [];
        foreach ($users as $user) {
            $temp = [];
            $temp["task_id"] = $taskId;
            $temp["resource_id"] = $user;
            $payload[] = $temp;
            $temp = [];
            // $exist = User::find($user);
            // if ($exist->status != 'busy') {

            //     $exist['status'] = 'busy';
            //     $exist->save();

            // }
        }
        if (count($payload) > 0) {
            $snap = HResourcesTask::insert($payload);
            if ($snap) {
                return [
                    "status" => true
                ];
            } else {
                return [
                    "status" => false,
                    "error" => "Error in inserting Resources!"
                ];
            }
        } else {
            return [
                "status" => false,
                "error" => "your specified human resources are busy!"
            ];
        }
        // dd($payload);

    }
    function nonhuman_resource_bridge_table($taskId, $resources)
    {
        $payload = [];
        foreach ($resources as $resource) {
            $temp = [];
            $temp["task_id"] = $taskId;
            $temp["resource_id"] = $resource;
            $payload[] = $temp;
            $temp = [];
            // $exist = NonHumanResources::find($resource);
            // if ($exist->status != 'busy') {
            //     $temp = [];
            //     $temp["task_id"] = $taskId;
            //     $temp["resource_id"] = $resource;
            //     $exist['status'] = 'busy';
            //     $exist->save();
            //     $payload[] = $temp;
            //     $temp = [];
            // }
        }
        if (count($payload) > 0) {
            $snap = NhResourcesTask::insert($payload);
            if ($snap) {
                return [
                    "status" => true
                ];
            } else {
                return [
                    "status" => false,
                    "error" => "Error in inserting Resources!"
                ];
            }
        } else {
            return [
                'status' => false,
                'error' => "all non human resources are busy!"
            ];
        }
        // dd($payload);

    }

    public function my_tasks()
    {
        try {
            $user = User::with('task')->where('id', auth()->user()->id)->get();
            // dd(auth()->user()->my_task);
            // foreach ($user as $u) {
            // // dd($u->task);
            // $s = $u->task::with("check");
            // dd($s);
            // }
            return response()->json([
                "payload" => auth()->user()->my_task
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "error" => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        try {
            $task = Task::find($id);
            $task->team;
            $task->resources;
            return response()->json([
                "payload" => $task
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            if (auth()->user()->can('edit task')) {
                $exist = Task::find($id);
                if ($exist) {
                    $this->validate($request, [
                        'name' => 'string',
                        'start_date' => 'date',
                        'end_date' => 'date'
                    ]);
                    $exist->fill($request->all());
                    if ($exist->save()) {
                        return response()->json([
                            "success" => true,
                            'payload' => $exist
                        ]);
                    } else {
                        return response()->json([
                            "success" => false,
                            'error' => 'Error in update'
                        ], 400);
                    }
                } else {
                    return response()->json([
                        "success" => false,
                        'error' => 'No such task exist!'
                    ], 404);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'payload' => "Unauthorized!"
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    function change_status(Request $request, $id)
    {
        try {
            // if (auth()->user()->can('edit task')) {
            $res = '';
            $this->validate($request, [
                'status' => "required|in:inProgress,inReview,bug,complete"
            ]);
            $exist = Task::find($id);
            if (!$exist) {
                return response()->json([
                    "success" => false,
                    "error" => "No such task exist!"
                ], 404);
            }
            // dd($exist->id);
            DB::beginTransaction();
            $check = HResourcesTask::where('resource_id', 6)->where('task_id', 18)->get();
            // dd(auth()->user()->id);
            $taskResource = HResourcesTask::where("resource_id", auth()->user()->id)
                ->where('task_id', $exist->id)->first();
            // dd($taskResource);


            switch ($request->status) {
                case 'inReview':
                    // dd($exist->id);
                    // dd($taskResource["sequence"] + 1,$exist);

                    $res = HResourcesTask::where('sequence', $taskResource["sequence"] + 1)
                        ->where('task_id', $exist->id)
                        ->update(["status" => "pending"]);
                    $taskResource["status"] = "complete";

                    $taskResource->save();
                    break;
                case 'bug':
                    $res = HResourcesTask::where('sequence', $taskResource["sequence"] - 1)
                        ->where('task_id', $exist->task_id)
                        ->update(["status" => "pending"]);
                    $taskResource["status"] = "complete";

                    $taskResource->save();
                    break;
                default:
            }
            $exist["status"] = $request->status;
            $saved = $exist->save();
            DB::commit();
            if ($saved) {

                return response()->json([
                    "success" => true,
                    'payload' => $exist
                ]);
            } else {
                return response()->json([
                    "success" => false,
                    'error' => 'Error in changing status'
                ], 400);
            }

            // if ($request->status == 'inReview' || $request->status == 'complete') {
            //     $team = $exist->team;
            //     $resources = $exist->resources;
            //     if ($team) {
            //         $this->toggle_status('free', $team, User::class);
            //         // foreach ($team as $member) {
            //         //     User::where('id', $member->id)->update(['status' => 'free']);
            //         // }
            //     }
            //     if ($resources) {
            //         $this->toggle_status('free', $resources, NonHumanResources::class);

            //         // foreach ($resources as $item) {
            //         //     NonHumanResources::where('id', $item->id)->update(['status' => 'free']);

            //     }
            // }
            // if ($request->status == 'bug') {
            //     $team = $exist->team;
            //     $resources = $exist->resources;
            //     if ($team) {
            //         $this->toggle_status('busy', $team, User::class);
            //         // foreach ($team as $member) {
            //         //     User::where('id', $member->id)->update(['status' => 'free']);
            //         // }
            //     }
            //     if ($resources) {
            //         $this->toggle_status('busy', $resources, NonHumanResources::class);

            //         // foreach ($resources as $item) {
            //         //     NonHumanResources::where('id', $item->id)->update(['status' => 'free']);

            //     }
            // }
            // $exist['status'] = $request->status;
            // if ($exist->save()) {
            //     return response()->json([
            //         "success" => true,
            //         'payload' => $exist
            //     ]);
            // }
            // } else {
            //     return response()->json([
            //         'success' => false,
            //         'payload' => "Unauthorized!"
            //     ], 401);
            // }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    function toggle_status($status, $list, $model)
    {
        if ($status == 'free') {
            foreach ($list as $item) {
                $model::where('id', $item->id)->update(["status" => $status]);
            }
        } else {
            foreach ($list as $item) {
                $model::where('id', $item->id)->where('status', 'free')->update(["status" => $status]);
            }
        }
    }



    public function assign_resources(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'humanResources' => 'required|array'
            ]);
            $errorMesages = [];
            $task = Task::find($id);
            $taskId = $task->id;
            // dd($taskId);
            if (!$task) {
                return response()->json([
                    "success" => false,
                    'error' => 'No such task exist!'
                ], 404);
            }
            if ($request->humanResources) {

                // dd(gettype($request->humanResources));
                $humanResourcesCollection = collect($request->humanResources)->map(function ($item) use ($taskId) {
                    $existingResource = HResourcesTask::where('task_id', $taskId)
                        ->where('resource_id', $item["resource_id"])
                        ->first();
                    if ($existingResource) {
                        $errorMesages[] = "already exist";
                    }
                    $resource = new HResourcesTask();
                    $resource["status"] = $item["sequence"] > 1 ? "notAssign" : "pending";
                    $resource["resource_id"] = $item["resource_id"];
                    $resource["task_id"] = $taskId;
                    $resource["created_at"] =  date('Y-m-d H:i:s');
                    $resource["updated_at"] =  date('Y-m-d H:i:s');
                    $resource["sequence"] =  $item["sequence"];
                    $resource["tag"] =  $item["tag"];
                    $resource->save();

                    // $exist = HResourcesTask::firstOrNew([
                    //     "task_id" => $taskId,
                    //     "resource_id" => $item["resource_id"]
                    // ]);
                    // $exist->fill((object)$item->all());
                    // dd($exist);
                    // if ($item["sequence"] > 1) {
                    //     // $item["status"] = "notAssign";
                    //     $exist["status"] = "notAssign";
                    // } else {
                    //     // $item["status"] = "pending";
                    //     $exist["status"] = "pending";
                    // }
                    // $item["task_id"] = $taskId;
                    // $item["created_at"] =  date('Y-m-d H:i:s');
                    // $item["updated_at"] =  date('Y-m-d H:i:s');
                    // $exist["task_id"] = $taskId;
                    // $exist["created_at"] =  date('Y-m-d H:i:s');
                    // $exist["updated_at"] =  date('Y-m-d H:i:s');
                    // $exist["sequence"] =  $item["sequence"];
                    // $exist["tag"] =  $item["tag"];

                    // $exist =(object) $item;
                    // dd(gettype($exist));
                    // return $item;
                });
                // dd(gettype($humanResourcesCollection));
                // $snap = HResourcesTask::insert($humanResourcesCollection->toArray());   [
                // "msg" => "Resource Assigned!"
                // ]
                if (count($errorMesages) > 0) {
                    return response()->json([
                        "status" => true,
                        "payload" => $errorMesages

                    ]);
                } else {
                    return response()->json([
                        "status" => true,
                        "payload" => [
                            "msg" => "Resource Assigned!"
                        ]
                    ]);
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            if (auth()->user()->can('delete task')) {
                $task = Task::find($id);
                if (!$task) {
                    return response()->json([
                        "success" => false,
                        'error' => 'No such task exist!'
                    ], 404);
                }

                if ($task->delete()) {
                    return response()->json([
                        "success" => true
                    ]);
                } else {
                    return response()->json([
                        "success" => false,
                        'error' => 'Error in delete'
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'payload' => "Unauthorized!"
                ], 401);
            }
        } catch (Exception $e) {
            // echo $e;
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
