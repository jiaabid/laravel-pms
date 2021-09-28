<?php

namespace App\Http\Controllers;

use App\Models\HResourcesTask;
use App\Models\NhResourcesTask;
use App\Models\Task;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
                    if ($this->human_resource_bridge_table($taskId, $request->humanResources)) {
                        if ($request->nonhumanResources) {
                            if ($this->nonhuman_resource_bridge_table($taskId, $request->nonhumanResources)) {
                                DB::commit();
                                return response()->json([
                                    'payload' => $task,
                                    'status' => true
                                ], 201);
                            }else{

                            }
                        }
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
        }
        // dd($payload);
        $snap = HResourcesTask::insert($payload);
        if ($snap) {
            return true;
        } else {
            return false;
        }
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
        }
        // dd($payload);
        $snap = NhResourcesTask::insert($payload);
        if ($snap) {
            return true;
        } else {
            return false;
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
        //
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
