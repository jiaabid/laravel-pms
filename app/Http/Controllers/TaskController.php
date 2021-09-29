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
                    'projectId' => "required|numeric",
                    'startDate' => "required|date",
                    'endDate' => 'required|date',
                    'humanResources' => 'required|array',
                    'nonhumanResources' => 'array',

                ]);


                $task = new Task();
                // dd($task);
                $task['name'] = $request->name;
                $task['start_date'] = $request->startDate;
                $task['end_date'] = $request->endDate;
                $task['project_id'] = $request->projectId;
                $task['description'] = $request->description ? $request->description : null;
                $task['created_by'] = auth()->user()->id;
                DB::beginTransaction();
                $saved = $task->save();
                //   dd($task->id);
                $taskId = $task->id;
                if ($saved) {
                    //insert in human resource bridge tabel
                    $this->responseBody = $this->human_resource_bridge_table($taskId, $request->humanResources);

                    if ($this->responseBody["status"]) {

                        //if non human resources then insert in bridge table
                        if ($request->nonhumanResources) {
                            $this->responseBody = $this->nonhuman_resource_bridge_table($taskId, $request->nonhumanResources);
                            if ($this->responseBody["status"]) {
                                DB::commit();
                                return response()->json([
                                    'payload' => $task,
                                    'status' => true
                                ], 201);
                            } else {
                                return response()->json($this->responseBody, 400);
                            }
                        }
                        DB::commit();
                        return response()->json([
                            'payload' => $task,
                            'status' => true
                        ], 201);
                    } else {
                        return response()->json($this->responseBody, 400);
                    }

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
            return response()->json([
                "payload" => auth()->user()->task
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
            if (auth()->user()->can('edit task')) {
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
             switch($request->status){
                 case 'inProgress'
             }




                if ($request->status == 'inReview' || $request->status == 'complete') {
                    $team = $exist->team;
                    $resources = $exist->resources;
                    if ($team) {
                        $this->toggle_status('free', $team, User::class);
                        // foreach ($team as $member) {
                        //     User::where('id', $member->id)->update(['status' => 'free']);
                        // }
                    }
                    if ($resources) {
                        $this->toggle_status('free', $resources, NonHumanResources::class);

                        // foreach ($resources as $item) {
                        //     NonHumanResources::where('id', $item->id)->update(['status' => 'free']);

                    }
                }
                if ($request->status == 'bug') {
                    $team = $exist->team;
                    $resources = $exist->resources;
                    if ($team) {
                        $this->toggle_status('busy', $team, User::class);
                        // foreach ($team as $member) {
                        //     User::where('id', $member->id)->update(['status' => 'free']);
                        // }
                    }
                    if ($resources) {
                        $this->toggle_status('busy', $resources, NonHumanResources::class);

                        // foreach ($resources as $item) {
                        //     NonHumanResources::where('id', $item->id)->update(['status' => 'free']);

                    }
                }
                $exist['status'] = $request->status;
                if ($exist->save()) {
                    return response()->json([
                        "success" => true,
                        'payload' => $exist
                    ]);
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



    public function assign_resoources(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'humanResources' => 'array',
                'nonHumanResources' => 'array'
            ]);
        } catch (Exception $e) {
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
        //
    }
}
