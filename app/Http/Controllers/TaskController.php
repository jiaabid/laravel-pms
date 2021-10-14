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
use App\Models\Issue;
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

            $tasks = Task::all();
            foreach ($tasks as $task) {
                $task->team;
                $task->resources;
            }
            if ($tasks) {
                return $this->success_response($tasks, 200);
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
                    return $this->success_response($task, 201);
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
    /**
     * retieve all the task realted to particular user
     *
     * @param  int $id
     *  @return \Illuminate\Http\Response
     */
    public function my_tasks($id)
    {
        try {
            // dd($mode);
            $payload = "";
            switch ($id) {
                    //individual tasks
                case 19:
                    $type = DbVariablesDetail::variableType('task_type')->value('individual')->first()->id;
                    $payload = Task::where('created_by', auth()->user()->id)->where('type', $type)->get();
                    break;
                    //particular task project assigned to the user
                case 20:
                    $type = DbVariablesDetail::variableType('task_type')->value('project')->first()->id;
                    // $payload = Task::where('project_id', 1)->where('type', 20)->user(auth()->user()->id)->get();
                    $payload = Task::with('team')->wherePivot('resource_id', auth()->user()->id)->where('project_id', 1)->where('type', 20)->get();
                    break;
                    //tasks assign by me
                case "assign":
                    $type = DbVariablesDetail::variableType('task_type')->value('project')->first()->id;
                    $payload = Task::where('created_by', auth()->user()->id)->where('type', $type)->get();
                    break;
                default:
                    $payload = auth()->user()->assigned_task()->where('project_id', 2)->get();
                    foreach ($payload as $i) {
                        $i->issues;
                    }
            }
            return $this->success_response($payload, 200);
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
            return $this->success_response($task, 200);
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
                        return $this->success_response($exist, 200);
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

    /**
     * task_action
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     *  @return \Illuminate\Http\Response
     */
    public function task_action(Request $request, $id)
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
                    //pause
                case 21:
                    if ($this->pause_task($exist)) {
                        return $this->success_response("Task has been paused", 200);
                    } else {
                        return $this->error_response('error in pausing the task status', 400);
                    }
                    break;
                    //resume id in DbVariableDetail table
                case 22:
                    if ($this->resume_task($exist)) {
                        return $this->success_response("Task has been resumed", 200);
                    } else {
                        return $this->error_response('error in pausing the task status', 400);
                    }
                    break;
            }
        } catch (Exception $e) {
        }
    }


    /**
     * change_status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function change_status(Request $request, $id)
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
           
            DB::beginTransaction();
            $taskResource = HResourcesTask::where("resource_id", auth()->user()->id)
                ->where('task_id', $exist->id)->first();
          

            $status = DbVariablesDetail::statusById($request->status)->first();
                      
            switch ($request->status) {

                    //inProgress
                    //inProgress == start , start the task
                case 4:
                    $this->start_task($taskResource);
                    $exist["status"] = $request->status;

                    break;

                    //complete
                case 5:
                    HResourcesTask::where('sequence', $taskResource["sequence"] + 1)
                        ->where('task_id', $exist->id)
                        ->update(["status" => DbVariablesDetail::variableType('task_status')->value('pending')->first()->id]);
                    $taskResource["status"] = $status->id;
                    $exist["status"] = DbVariablesDetail::variableType('task_status')->value('inReview')->first()->id;
                    $taskResource["end_at"] = Carbon::now("Asia/Karachi")->toDateTimeString();
                    $taskResource["total_effort"] =  $this->calculate_effort($taskResource);
                    if ($taskResource["total_effort"] > $taskResource["estimated_effort"]){
                        $taskResource["delay"] = true;
                    }
                    $taskResource->save();
                    break;

                    //issue
                case 7:
                    for ($i = $taskResource["sequence"] - 1; $i > 0; $i--) {
                        HResourcesTask::where('sequence', $i)
                            ->where('task_id', $exist->id)
                            ->update(["status" => DbVariablesDetail::variableType('task_status')->value('pending')->first()->id]);
                    }
                    $taskResource["status"] = DbVariablesDetail::variableType('task_status')->value('completed')->first()->id;
                    $exist["status"] = $request->status;
                   
                    $this->mark_issue($request->issues, $exist->id);
                    $taskResource->save();
                    break;

                    //approve
                case 9:
                    $exist["status"] = DbVariablesDetail::variableType('task_status')->value('completed')->first()->id;
                    $taskResource["status"] = $exist["status"];
                    $taskResource["end_at"] = Carbon::now("Asia/Karachi")->toDateTimeString();
                    $taskResource["total_effort"] =  $this->calculate_effort($taskResource);
                    if ($taskResource["total_effort"] > $taskResource["estimated_effort"]){
                        $taskResource["delay"] = true;
                    }
                    $taskResource->save();
                    break;
                default:
                    
                    break;
            }
          
            $saved = $exist->save();
            DB::commit();
            if ($saved) {
                return $this->success_response($exist, 200);
            } else {
                return $this->error_response('Error in changing status', 400);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }



    /**
     * open issues on particular task and add into storage
     *
     * @param  mixed $issues
     * @param  int $taskId
     * @return void
     */
    protected function mark_issue($issues, $taskId)
    {

        $errors = collect([]);
        //insert the issues in issue table
        foreach ($issues as $issue) {

            $issueExist = Issue::where('task_id', $taskId)->where('name', $issue["name"])->first();
            if (!$issueExist) {
                $newIssue = new Issue();
                $newIssue["name"] = $issue["name"];
                $newIssue["description"] = isset($issue["description"]) ? $issue["description"] : null;
                $newIssue["task_id"] = $taskId;
                $newIssue["created_by"] = auth()->user()->id;
                $saved = $newIssue->save();
                if (!$saved) {
                    $errors->push([
                        "name" => $issue["name"],
                        "notsaved" => true
                    ]);
                }
            }
        }
    }


    /**
     * assign_resources
     *
     *@param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
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
                        $resource["status"] = $item["sequence"] > 1 ? DbVariablesDetail::variableType('task_status')->value('notAssign')->first()->id : DbVariablesDetail::variableType('task_status')->value('pending')->first()->id;
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
                        return $this->success_response(["msg" => "resource assigned!"], 200);
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
                    return $this->success_response([], 200);
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

    /**
     * mark the task inprogress , as started and add the start time to specfied task
     *
     * @param  HResourceTask $item
     * @return bool
     */
    public function start_task($item)
    {
        try {
            $date = Carbon::now("Asia/Karachi")->toDateTimeString();
            $item["start_at"] = $date;
            $item["status"] = DbVariablesDetail::variableType('task_status')->value('inProgress')->first()->id;
            
            if ($item->save()) {
               
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }

    /**
     *pause the task and calculate the effort
     *
     * @param  HResourceTask $item
     * @return bool
     */
    function pause_task($item)
    {
        try {
            $item["pause"] = true;
            $item["end_at"] = Carbon::now("Asia/Karachi")->toDateTimeString();
            $item["total_effort"] =  $this->calculate_effort($item);
            if ($item["total_effort"] > $item["estimated_effort"]) {
                $item["delay"] = true;
            }
            if ($item->save()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
        }
    }

    /**
     * resume the task
     *
     * @param  HResourceTask $item
     * @return bool
     */
    function resume_task($item)
    {
        $item["pause"] = false;
        $item["start_at"] = Carbon::now("Asia/Karachi")->toDateTimeString();

        $item["total_effort"] = $this->calculate_effort($item);
        if ($item["total_effort"] > $item["estimated_effort"]) {
            $item["delay"] = true;
        }
        $item["end_at"] = null;
        if ($item->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * calculate the total effort on basis of task start and end time
     *
     * @param  HResourceTask $item
     * @return float
     */
    public function calculate_effort($item)
    {


        return $item["total_effort"] != null
            ? abs($item["total_effort"] + abs(((strtotime($item["start_at"]) - strtotime($item["end_at"])) / 60 / 60)))
            : abs(((strtotime($item["start_at"]) - strtotime($item["end_at"])) / 60 / 60));
    }
}
