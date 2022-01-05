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
use App\Models\Project;
use App\Models\TagStatus;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;


date_default_timezone_set('Asia/Karachi');

class TaskController extends Controller
{
    use ResponseTrait, ReusableTrait;
    private $responseBody;




    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'type' => 'required'
        ]);

        if (auth()->user()->can('create task') && $request->type == 18) {
            //    dd($request->input('start_date'));

            $this->validate($request, [
                'name' => "required|min:3|string",
                'project_id' => "required|numeric",
                'start_date' => "required|date",
                'end_date' => 'required|date'
            ]);

            $task = new Task();
            $task['name'] = $request->name;
            $task['start_date'] = $request->start_date;
            $task['end_date'] = $request->end_date;
            $task['project_id'] = $request->project_id;
            $task['type'] = $request->type;
            $task['description'] = $request->description ? $request->description : '';
            $task['created_by'] = auth()->user()->id;
            DB::beginTransaction();
            $saved = $task->save();

            if ($saved) {
                DB::commit();
                return $this->success_response($task, 201);
            } else {
                return $this->error_response("Error in saving", 400);
            }
        } else if ($request->type == 17) {
            $this->validate($request, [
                'name' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date'
            ]);
            $task = new Task();
            $task['name'] = $request->name;
            $task['start_date'] = $request->start_date;
            $task['end_date'] = $request->end_date;
            $task['type'] = $request->type;
            $task['description'] = $request->description ? $request->description : '';
            $task['created_by'] = auth()->user()->id;
            DB::beginTransaction();
            $saved = $task->save();

            if ($saved) {
                DB::commit();
                return $this->success_response($task, 201);
            } else {
                return $this->error_response("Error in saving", 400);
            }
        } else {
            return $this->error_response("Forbidden!", 403);
        }
    }

    //retrieving tasks assigned ,created , assign to others    
    /**
     * retieve all the task realted to particular user
     *
     * @param  int $id
     *  @return \Illuminate\Http\Response
     */
    public function my_tasks(Request $request, $id)
    {

        $payload = [];
        if (auth()->user()->can('retrieve task')) {

            switch ($id) {
                    //individual tasks
                case 17:
                    $myTasks = Task::where('created_by', auth()->user()->id)->where('type', $id)->where('deleted_at', null)->get();
                    $payload["myTasks"] = $myTasks;
                    break;

                    //tasks assign by me and assignTo me
                case 18:
                    $pid = $request->query('pid');
                    // $allTask = Project::where('id', $request->query('pid'))->where('created_by', auth()->user()->id)->with('tasks')->first();
                    // if ($allTask) {
                    //     $allTask = $allTask->tasks;
                    // } else {
                    //     $allTask = [];
                    // }
                    $roles = collect($this->get_child_roles(auth()->user()));

                    $childUsers = User::whereIn('role_id', $roles)->get()->pluck('id');
                    $childUsers->push(auth()->user()->id);
                    $allTask = Task::with('team')->whereHas('team', function ($query) use ($childUsers) {
                        return $query->whereIn('resource_id', $childUsers);
                    })->with('issues')->get();
                    foreach ($allTask as $item) {

                        $item->team;
                        foreach ($item->team as $member) {
                            $member->detail->tagId;
                        }
                    }
                    // return $hirarchalTask;
                    $assignByMe = Task::with('issues')->where('created_by', auth()->user()->id)->where('type', $id)->where('project_id', $pid)->where('deleted_at', null)->get();
                    $assignToMe = auth()->user()->assigned_task()->where('project_id', $pid)->get();
                    foreach ($assignByMe as $item) {

                        $item->team;
                        foreach ($item->team as $member) {
                            $member->detail->tagId;
                        }
                        $item['assignByMe'] = true;
                    }
                    foreach ($assignToMe as $item) {
                        $item->issues;
                        $item->team;
                        foreach ($item->team as $member) {
                            $member->detail->tagId;
                        }
                        $item['assignByMe'] = false;
                    }
                    foreach ($allTask as $item) {
                        $item->issues;
                        $item->team;
                        foreach ($item->team as $member) {
                            $member->detail->tagId;
                        }
                    }
                    // $payload["assignedToMe"] = $assignToMe;
                    // $payload["assignedByMe"] = $assignByMe;
                    $payload['tasks'] = collect([]);
                    $payload['tasks']->push(...$assignByMe);
                    $payload['tasks']->push(...$assignToMe);
                    $payload['allTasks'] = $allTask;
                    break;

                default:
                    $tasks = Task::with('issues')->where('project_id', $request->query('pid'))->where('deleted_at', null)->get();
                    foreach ($tasks as $task) {
                        $task->team;
                    }
                    $payload['tasks'] = $tasks;
                    break;
            }
            return $this->success_response($payload, 200);
        } else {
            $myTasks = Task::where('created_by', auth()->user()->id)->where('type', DbVariablesDetail::variableType('task_type')->variableValue('individual')->first()->id)->get();
            $payload["myTasks"] = $myTasks;
            return $this->success_response($payload, 200);
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

        $task = Task::where('id', $id)->with('team')->with('issues')->with('creator:id,name')->first();
        // $task->creator;
        foreach ($task->team as $member) {
            $member->detail->tagId;
        }
        // $first= $task->team[0];
        // return $first;
        //  $first->pivot->tagId;
        // return $task;
        // $task = Task::find($id);
        if (!$task) {
            return $this->error_response('Not found', 404);
        }
        // $task->team;
        // $task->issues;
        return $this->success_response($task, 200);
    }


    public function update_mytask(Request $request, $id)
    {

        // if (auth()->user()->can('edit task')) {
        $exist = Task::find($id);
        if ($exist) {
            // $this->validate($request, [
            //     'name' => 'string',
            //     'start_date' => 'date',
            //     'end_date' => 'date'
            // ]);

            $exist['name'] = $request->name;
            $exist['description'] = $request->description;
            $exist['start_date'] = $request->start_date;
            $exist['end_date'] = $request->end_date;
            // $exist->fill($request->all());

            if ($exist->save()) {
                return $this->success_response($exist, 200);
            } else {
                return $this->error_response("Error in updating", 400);
            }
        } else {
            return $this->error_response('No such task exist!', 404);
        }
        // } else {
        //     return $this->error_response("Forbidden!", 403);
        // }
    }

    protected function update_resource($humanResources, $taskId)
    {
        collect($humanResources)->map(function ($item) use ($taskId) {
            $resource = HResourcesTask::where('task_id', $taskId)
                ->where('resource_id', $item["old_resource"])
                ->where('tag_id', $item['tag_id'])
                ->first();

            if ($resource) {
                // $existingResource->delete();
                // return $errorMesages->push($existingResource->resource_id . " already exist");;
                $resource["resource_id"] = $item["resource_id"];
                $resource["task_id"] = $taskId;
                $resource["sequence"] =  $item["sequence"];
                $resource["tag_id"] =  $item["tag_id"];
                $resource["estimated_effort"] =  $item["estimated_effort"];
                $resource["start_date"] =  $item["start_date"];
                $resource["end_date"] =  $item["end_date"];
                $resource["updated_at"] =  date('Y-m-d H:i:s');
                $resource->save();
            }
        });
        return true;
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

        if (auth()->user()->can('edit task')) {
            $exist = Task::where('id', $id)->with('team')->first();
            if ($exist) {
                // $this->validate($request, [
                //     'name' => 'string',
                //     'start_date' => 'date',
                //     'end_date' => 'date'
                // ]);
                // $exist->fill($request->all());
                DB::beginTransaction();
                $exist['name'] = $request->name ? $request->name : $exist['name'];
                $exist['description'] = $request->description;
                $exist['start_date'] = $request->start_date ? $request->start_date : $exist['start_date'];
                $exist['end_date'] = $request->end_date ? $request->end_date : $exist['end_date'];
                foreach ($exist->team as $member) {
                    $member->detail->tagId;
                }
                if ($request->humanResources && count($request->humanResources) > 0) {
                    $assigned = $this->update_resource($request->humanResources, $exist->id);
                    if ($assigned) {
                        $exist->save();
                        DB::commit();
                        // $exist->team;
                        return $this->success_response($exist, 200);
                    }
                }
                $saved = $exist->save();
                DB::commit();
                if ($saved) {

                    return $this->success_response($exist, 200);
                } else {
                    return $this->error_response("Error in updating", 400);
                }
            } else {
                return $this->error_response('No such task exist!', 404);
            }
        } else {
            return $this->error_response("Forbidden!", 403);
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
            case 19:
                if ($this->pause_task($exist)) {
                    return $this->success_response("Task has been paused", 200);
                } else {
                    return $this->error_response('error in pausing the task status', 400);
                }
                break;
                //resume id in DbVariableDetail table
            case 20:
                if ($this->resume_task($exist)) {
                    return $this->success_response("Task has been resumed", 200);
                } else {
                    return $this->error_response('error in pausing the task status', 400);
                }
                break;
        }
    }

    public function my_task_change_status(Request $request)
    {
        $this->validate($request, [
            'status' => "required"
        ]);
        $exist = Task::find($request->id);
        if (!$exist) {
            return $this->error_response('No such task exist!', 404);
        }
        $exist['status'] = $request->status;

        if ($exist->save()) {
            return $this->success_response($exist, 200);
        } else {
            return $this->error_response('Error in changing status', 400);
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
            ->where('task_id', $exist->id)->where('tag_id', $request->myTag)->where('deleted_at', null)->first();

        if ($taskResource !== null) {
            $status = DbVariablesDetail::statusById($request->status)->first();
            // dd($taskResource);
            switch ($request->status) {

                    //inProgress
                    //inProgress == start , start the task
                case 12:
                    $this->start_task($taskResource);
                    if ($taskResource->sequence == 1) {
                        $exist["status"] = $request->status;
                    }


                    break;

                    //complete
                case 15:
                    //if dev
                    // if ($taskResource->tag_id == 7) {
                    if (!TagStatus::where('tag_id', $taskResource->tag_id)->where('status_id', 13)->first()) {
                        $unresolvedTask = Issue::where('task_id', $id)->where('tag_id', $taskResource->tag_id)->where('status', 21)->get();
                        if (count($unresolvedTask) > 0) {
                            return $this->error_response("Cant complete you have unresolved tasks!", 400);
                        }
                        if (count(HResourcesTask::where('sequence', $taskResource["sequence"] + 1)
                            ->where('task_id', $exist->id)->get())) {
                            HResourcesTask::where('sequence', $taskResource["sequence"] + 1)
                                ->where('task_id', $exist->id)
                                ->update(["status" => DbVariablesDetail::variableType('task_status')->variableValue('pending')->first()->id]);
                            $exist["status"] = DbVariablesDetail::variableType('task_status')->variableValue('inReview')->first()->id;
                        } else {
                            $exist["status"] = DbVariablesDetail::variableType('task_status')->variableValue('completed')->first()->id;
                        }
                        $taskResource["status"] = $status->id;
                        $taskResource["end_at"] = Carbon::now("Asia/Karachi")->toDateTimeString();
                        $taskResource["total_effort"] =  $this->calculate_effort($taskResource);
                        $taskResource->save();
                        break;
                    } else {
                        //if qa
                        $unapprovedTask = Issue::where('task_id', $id)->where('created_by', $request->myTag)->where('approved', false)->where('status', 22)->get();
                        if (count($unapprovedTask) > 0) {
                            return $this->error_response("Cant complete you have unapproved tasks!", 400);
                        }
                        $myUnresolvedTask = Issue::where('task_id', $id)->where('tag_id', $taskResource->tag_id)->where('sequence_no', $taskResource->sequence)->where('status', 21)->count();
                        if ($myUnresolvedTask !== 0) {
                            return $this->error_response("Cant complete you have unresolved tasks!", 400);
                        }
                        $unresolvedTask = Issue::where('task_id', $id)->where('created_by', $request->myTag)->where('status', 21)->where('deleted_at', null)->get();
                        if (count($unresolvedTask) > 0) {
                            // for ($i = $taskResource["sequence"] - 1; $i > 0; $i--) {
                            HResourcesTask::where('sequence',  $taskResource["sequence"] - 1)
                                ->where('task_id', $exist->id)
                                ->where('deleted_at', null)
                                ->update(["status" => DbVariablesDetail::variableType('task_status')->variableValue('pending')->first()->id]);
                            // }
                        } else {
                            if (count(HResourcesTask::where('sequence', $taskResource["sequence"] + 1)
                                ->where('task_id', $exist->id)->get()) > 0) {
                                HResourcesTask::where('sequence', $taskResource["sequence"] + 1)
                                    ->where('task_id', $exist->id)
                                    ->update(["status" => DbVariablesDetail::variableType('task_status')->variableValue('pending')->first()->id]);
                                $exist["status"] = DbVariablesDetail::variableType('task_status')->variableValue('inReview')->first()->id;
                            } else {
                                $exist["status"] = DbVariablesDetail::variableType('task_status')->variableValue('completed')->first()->id;
                            }
                        }
                        $taskResource["status"] = DbVariablesDetail::variableType('task_status')->variableValue('completed')->first()->id;
                        $taskResource["status"] = $status->id;

                        $taskResource["end_at"] = Carbon::now("Asia/Karachi")->toDateTimeString();
                        $taskResource["total_effort"] =  $this->calculate_effort($taskResource);
                        $taskResource->save();
                        break;
                    }


                    break;

                    //issue
                case 13:

                    $exist["status"] = $request->status;

                    $this->mark_issue($request->issues, $exist->id, $request->myTag);
                    break;

                    //approve
                case 16:
                    $unapprovedTask = Issue::where('task_id', $id)->where('created_by', $request->myTag)->where('approved', false)->get();
                    if (count($unapprovedTask) > 0) {
                        return $this->error_response("Cant complete you have unapproved tasks!", 400);
                    }
                    $exist["status"] = DbVariablesDetail::variableType('task_status')->variableValue('completed')->first()->id;
                    $taskResource["status"] = $exist["status"];
                    $taskResource["end_at"] = Carbon::now("Asia/Karachi")->toDateTimeString();
                    $taskResource["total_effort"] =  $this->calculate_effort($taskResource);
                    if ($exist["end_date"] < Carbon::now("Asia/Karachi")->toDateTimeString() && $exist["status"] ==  DbVariablesDetail::variableType('task_status')->variableValue('completed')->first()->id) {

                        $exist["delay"] = true;
                    }
                    $taskResource->save();
                    break;
                default:

                    break;
            }
            // return $exist;
            $saved = $exist->save();
            DB::commit();
            $task = auth()->user()->assigned_task()->where('task_id', $exist->id)->where('project_id', $exist->project_id)->first();
            $task->creator;
            $task->issues;
            $task->team;
            foreach ($task->team as $member) {
                $member->detail->tagId;
            }
            // return $task;
            // $exist->issues;
            // $exist->team;
            // $exist->detail;
            // foreach ($exist->team as $member) {
            //     $member->detail->tagId;
            // }

            if ($saved) {
                return $this->success_response($task, 200);
            } else {
                return $this->error_response('Error in changing status', 400);
            }
        } else {
            return $this->error_response('Unauthorized!', 403);
        }
    }

    /**
     * open issues on particular task and add into storage
     *
     * @param  mixed $issues
     * @param  int $taskId
     * @return void
     */
    protected function mark_issue($issues, $taskId, $myTag)
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
                // $newIssue["resource_id"] = $issue['resource_id'];
                $newIssue['tag_id'] = $issue['tag_id'];
                $newIssue['sequence_no'] = $issue['sequence_no'];
                $newIssue["created_by"] = $myTag;
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
            // return $this->success_response($request->humanResources,200);
            if ($request->humanResources) {

                $humanResourcesCollection = collect($request->humanResources)->map(function ($item) use ($taskId, $errorMesages) {
                    $existingResource = HResourcesTask::where('task_id', $taskId)
                        ->where('resource_id', $item["resource_id"])
                        ->first();

                    // if ($existingResource) {
                    //     $existingResource->delete();
                    //     // return $errorMesages->push($existingResource->resource_id . " already exist");;
                    // }

                    $resource = new HResourcesTask();
                    $resource["status"] = $item["sequence"] > 1 ? DbVariablesDetail::variableType('task_status')->variableValue('notAssign')->first()->id : DbVariablesDetail::variableType('task_status')->variableValue('pending')->first()->id;
                    $resource["resource_id"] = $item["resource_id"];
                    $resource["task_id"] = $taskId;
                    $resource["sequence"] =  $item["sequence"];
                    $resource["tag_id"] =  $item["tag_id"];
                    $resource["estimated_effort"] =  $item["estimated_effort"];
                    $resource["start_date"] =  $item["start_date"];
                    $resource["end_date"] =  $item["end_date"];
                    $resource["created_at"] =  date('Y-m-d H:i:s');
                    $resource["updated_at"] =  date('Y-m-d H:i:s');
                    $resource->save();
                });
                return $this->success_response(["msg" => "resource assigned!"], 200);

                // if (count($errorMesages) > 0) {
                //     return $this->error_response($errorMesages, 400);
                // } else {
                // }
            }
        } else {
            return $this->error_response("Forbidden!", 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if ($request->query('my')) {
            $task = Task::where('id', $id)->where('type', 17)->first();
            if (!$task) {
                return $this->error_response('No such task exist!', 404);
            }

            if ($task->delete()) {
                return $this->success_response($task, 200);
            } else {
                return $this->error_response('Error in delete', 400);
            }
        } else if (auth()->user()->can('delete task')) {
            $task = Task::find($id);
            if (!$task) {
                return $this->error_response('No such task exist!', 404);
            }
            try {
                DB::beginTransaction();
                $deleted = $task->delete();
                HResourcesTask::where('task_id', $id)->delete();
                DB::commit();
                if ($task->delete()) {
                    return $this->success_response($task, 200);
                } else {
                    return $this->error_response('Error in delete', 400);
                }
            } catch (Exception $e) {
                return $this->error_response($e->getMessage(), 500);
            }
        } else {
            return $this->error_response("Forbidden!", 403);
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
        // dd($item);
        $date = Carbon::now("Asia/Karachi")->toDateTimeString();
        $item["start_at"] = $date;
        $item["status"] = DbVariablesDetail::variableType('task_status')->variableValue('inProgress')->first()->id;
        // dd(gettype($item));
        if ($item->save()) {

            return true;
        } else {
            return false;
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

        $item["pause"] = true;
        $item["end_at"] = Carbon::now("Asia/Karachi")->toDateTimeString();
        $item["total_effort"] =  $this->calculate_effort($item);
        if ($item["total_effort"] > ($item["estimated_effort"] / (60 * 60))) {
            $item["delay"] = true;
        }
        if ($item->save()) {
            return true;
        } else {
            return false;
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
        if ($item["total_effort"] > ($item["estimated_effort"] / (60 * 60))) {
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
            ? abs($item["total_effort"] + abs(((strtotime($item["start_at"]) - strtotime($item["end_at"])) / (60 * 60))))
            : abs(((strtotime($item["start_at"]) - strtotime($item["end_at"])) / (60 * 60)));
    }
}
