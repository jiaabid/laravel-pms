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
use App\Http\Traits\ReusableTrait;
use App\Http\Traits\ResponseTrait;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

date_default_timezone_set('Asia/Karachi');

class TaskController extends Controller
{
    use ResponseTrait;
    private $responseBody;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // $tasks = Task::with('team')->with('resources')->get();
            $tasks = Task::all();
            foreach ($tasks as $task) {
                $task->team;
                $task->resources;
            }
            if ($tasks) {
                return $this->ok_response($tasks, 200);
            } else {
                return $this->error_response("Not found!", 404);
            }
        } catch (Exception $e) {
            return $this->error_response("No user exist!", 404);
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
                    return $this->ok_response($task, 201);
                } else {
                    return $this->error_response("Error in saving", 400);
                }
            } else {
                return $this->error_response("Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    //retrieving tasks assigned ,created , assign to others
    public function my_created_tasks(string $mode)
    {
        try {
            // dd($mode);
            $payload = "";
            switch ($mode) {
                case "my":
                    $type = DbVariablesDetail::id('task_type')->status('my')->first()->id;
                    $payload = Task::where('created_by', auth()->user()->id)->where('type', $type)->get();
                    break;

                    //tasks assign by me
                case "assign":
                    $type = DbVariablesDetail::id('task_type')->status('project')->first()->id;
                    $payload = Task::where('created_by', auth()->user()->id)->where('type', $type)->get();
                    break;
                default:
                    $payload = auth()->user()->assigned_task;
            }
            // $user = User::with('task')->where('id', auth()->user()->id)->first();
            // dd($user) ;
            return $this->ok_response($payload, 200);
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
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
            return $this->ok_response($task, 200);
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
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
                        return $this->ok_response($exist, 200);
                    } else {
                        return $this->error_response("Error in updating", 400);
                    }
                } else {
                    return $this->error_response('No such task exist!', 404);
                }
            } else {
                return $this->error_response("Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    function task_action(Request $request, $id)
    {
        try {
            // dd("in task action");
            $this->validate($request, [
                'mode' => 'required'
            ]);
            $exist = HResourcesTask::where('resource_id', auth()->user()->id)->where('task_id', $id)->first();
            //   dd($exist);
            if (!$exist) {
                return $this->error_response("No such entry exist", 404);
            }
            switch ($request->mode) {
                case 'start':
                    if ($this->start_task($exist)) {
                        return $this->ok_response("Task has been started", 200);
                    } else {
                        return $this->error_response('error in changing the task status', 400);
                    }
                    break;
                case 'pause':
                    if ($this->pause_task($exist)) {
                        return $this->ok_response("Task has been paused", 200);
                    } else {
                        return $this->error_response('error in pausing the task status', 400);
                    }
                    break;
                case 'resume':
                    if ($this->resume_task($exist)) {
                        return $this->ok_response("Task has been resumed", 200);
                    } else {
                        return $this->error_response('error in pausing the task status', 400);
                    }
                    break;
            }
        } catch (Exception $e) {
        }
    }

    function start_task($item)
    {
        try {
            $date = Carbon::now("Asia/Karachi")->toDateTimeString();
            $item["start_at"] = $date;
            $item["status"] = DbVariablesDetail::id('task_status')->status('inProgress')->first()->id;
            if ($item->save()) {
                // dd($item);
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    function pause_task($item)
    {
        try {
            $item["pause"] = true;
            $item["end_at"] = Carbon::now("Asia/Karachi")->toDateTimeString();
            $item["total_effort"] = $item["total_effort"] != null
                ? abs($item["total_effort"] + abs(((strtotime($item["start_at"]) - strtotime($item["end_at"])) / 60 / 60)))
                : abs(((strtotime($item["start_at"]) - strtotime($item["end_at"])) / 60 / 60));
            if ($item->save()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
        }
    }

    function resume_task($item)
    {
        $item["pause"] = false;
        $item["start_at"] = Carbon::now("Asia/Karachi")->toDateTimeString();

        $item["total_effort"] = $item["total_effort"] != null
            ? abs($item["total_effort"] + abs(((strtotime($item["start_at"]) - strtotime($item["end_at"])) / 60 / 60)))
            : abs(((strtotime($item["start_at"]) - strtotime($item["end_at"])) / 60 / 60));
        $item["end_at"] = null;
        if ($item->save()) {
            return true;
        } else {
            return false;
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
                return $this->error_response('No such task exist!', 404);
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
                return $this->ok_response($exist, 200);
            } else {
                return $this->error_response('Error in changing status', 400);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
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

                if (!$task) {
                    return $this->error_response('No such task exist!', 404);
                }
                if ($request->humanResources) {

                    $humanResourcesCollection = collect($request->humanResources)->map(function ($item) use ($taskId, $errorMesages) {
                        $existingResource = HResourcesTask::where('task_id', $taskId)
                            ->where('resource_id', $item["resource_id"])
                            ->first();
                        if ($existingResource) {
                            return $errorMesages->push($existingResource->resource_id . " already exist");;
                        }
                        $resource = new HResourcesTask();
                        $resource["status"] = $item["sequence"] > 1 ? DbVariablesDetail::id('task_status')->status('notAssign')->first()->id : DbVariablesDetail::id('task_status')->status('pending')->first()->id;
                        $resource["resource_id"] = $item["resource_id"];
                        $resource["task_id"] = $taskId;
                        $resource["created_at"] =  date('Y-m-d H:i:s');
                        $resource["updated_at"] =  date('Y-m-d H:i:s');
                        $resource["sequence"] =  $item["sequence"];
                        $resource["tag"] =  $item["tag"];
                        $resource["estimated_effort"] =  $item["estimated_effort"];
                        $resource["start_date"] =  $item["start_date"];
                        $resource["end_date"] =  $item["end_date"];
                        $resource->save();
                    });

                    if (count($errorMesages) > 0) {
                        return $this->error_response($errorMesages, 400);
                    } else {
                        return $this->ok_response(["msg" => "resource assigned!"], 200);
                    }
                }
            } else {
                return $this->error_response("Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
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
                    return $this->error_response('No such task exist!', 404);
                }

                if ($task->delete()) {
                    return $this->ok_response([], 200);
                } else {
                    return $this->error_response('Error in delete', 400);
                }
            } else {
                return $this->error_response("Unauthorized!", 401);
            }
        } catch (Exception $e) {
            // echo $e;
            return $this->error_response($e->getMessage(), 500);
        }
    }
}
