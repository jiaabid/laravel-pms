<?php

namespace App\Http\Controllers;

use App\Models\DbVariablesDetail;
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
                    'project_id' => "numeric",
                    'start_date' => "required|date",
                    'end_date' => 'required|date',
                    'type' => 'required'


                ]);

                $task = new Task();
                // dd($task);
                $task['name'] = $request->name;
                $task['start_date'] = $request->start_date;
                $task['end_date'] = $request->end_date;
                $task['project_id'] = $request->project_id ? $request->project_id : null;
                $task['type'] = $request->type;
                $task['description'] = $request->description ? $request->description : '';
                $task['created_by'] = auth()->user()->id;
                // dd($task);
                DB::beginTransaction();
                $saved = $task->save();
                //   dd($task->id);
                $taskId = $task->id;
                if ($saved) {

                    DB::commit();
                    return response()->json([
                        'payload' => $task,
                        'status' => true
                    ], 201);
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




    // dd($payload);


    public function my_created_tasks(string $mode)
    {
        try {
            // dd($mode);
            $payload = "";
            switch ($mode) {
                case "my":
                    $type = DbVariablesDetail::id('task_type')->status('my')->first()->id;
                    $payload = Task::where('created_by', auth()->user()->id)->where('type', $type)->get();
                    // dd($type);
                    break;
                case "assign":
                    $type = DbVariablesDetail::id('task_type')->status('project')->first()->id;
                    $payload = Task::where('created_by', auth()->user()->id)->where('type', $type)->get();
                    break;
                default:
                    $payload = auth()->user()->assigned_task;
            }
            $user = User::with('task')->where('id', auth()->user()->id)->first();
            // dd($user) ;
            return response()->json([
                "payload" => $payload
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
                'status' => "required"
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

            $status = DbVariablesDetail::statusById($request->status)->first();
            //  dd($status);          
            switch ($status->value) {
                case 'completed':
                    // dd($exist->id);
                    // dd($taskResource["sequence"] + 1,$exist);
                    $res = HResourcesTask::where('sequence', $taskResource["sequence"] + 1)
                        ->where('task_id', $exist->id)
                        ->update(["status" => DbVariablesDetail::id('task_status')->status('pending')->first()->id]);
                    $taskResource["status"] = $status->id;
                    $exist["status"] = DbVariablesDetail::id('task_status')->status('inReview')->first()->id;

                    $taskResource->save();
                    break;
                case 'bug':
                    $res = HResourcesTask::where('sequence', $taskResource["sequence"] - 1)
                        ->where('task_id', $exist->task_id)
                        ->update(["status" => DbVariablesDetail::id('task_status')->status('pending')->first()->id]);
                    $taskResource["status"] = DbVariablesDetail::id('task_status')->status('completed')->first()->id;
                    $exist["status"] = $status->id;

                    $taskResource->save();
                    break;
                case 'approve':
                    $exist["status"] = DbVariablesDetail::id('task_status')->status('completed')->first()->id;

                    break;
                default:
                    $exist["status"] = $status->id;
                    break;
            }
            // $exist["status"] = $request->status;
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





    public function assign_resources(Request $request, $id)
    {
        try {
            if (auth()->user()->can('assign task')) {
                $this->validate($request, [
                    'humanResources' => 'required|array'
                ]);
                $errorMesages = collect([]);
                $task = Task::find($id);
                $taskId = $task->id;
                // dd($taskId);
                if (!$task) {
                    return response()->json([
                        "success" => false,
                        'error' => 'No such task exist!'
                    ], 404);
                }


                // dd(DbVariablesDetail::id('task_status')->status('pending')->first()->id);

                // dd(DbVariablesDetail::with('variable')->get());
                if ($request->humanResources) {

                    // dd(gettype($request->humanResources));
                    $humanResourcesCollection = collect($request->humanResources)->map(function ($item) use ($taskId, $errorMesages) {
                        $existingResource = HResourcesTask::where('task_id', $taskId)
                            ->where('resource_id', $item["resource_id"])
                            ->first();
                        if ($existingResource) {
                            return $errorMesages->push($existingResource->resource_id . " already exist");
                            // $errorMesages[] = "already exist";
                        }
                        $resource = new HResourcesTask();
                        $resource["status"] = $item["sequence"] > 1 ? DbVariablesDetail::id('task_status')->status('notAssign')->first()->id : DbVariablesDetail::id('task_status')->status('pending')->first()->id;
                        $resource["resource_id"] = $item["resource_id"];
                        $resource["task_id"] = $taskId;
                        $resource["created_at"] =  date('Y-m-d H:i:s');
                        $resource["updated_at"] =  date('Y-m-d H:i:s');
                        $resource["sequence"] =  $item["sequence"];
                        $resource["tag"] =  $item["tag"];
                        $resource->save();
                    });

                    if (count($errorMesages) > 0) {
                        return response()->json([
                            "status" => false,
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
