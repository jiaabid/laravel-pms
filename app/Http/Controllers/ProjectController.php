<?php

namespace App\Http\Controllers;

use App\Http\Traits\ReusableTrait;
use App\Models\DbVariables;
use App\Models\Project;
use App\Models\ProjectResource;
use App\Models\Task;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ResponseTrait;
use App\Models\DbVariablesDetail;
use App\Models\Employee;
use App\Models\NonHumanResources;

class ProjectController extends Controller
{

    use ReusableTrait, ResponseTrait;
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // return auth()->user()->can('create project');
        if ((auth()->user()->can('retrieve project') && auth()->user()->admin)) {

            //retrieve child roles
            $roles = $this->get_child_roles(auth()->user());
            $roles->push(auth()->user()->role_id);
            // return auth()->user()->projects;
            //   return $roles;

            // if ($request->query("all") == "true") {
            //     //get my projects and my child projects
            //     $projects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
            //         return $query->whereIn('role_id', $roles);
            //     })->get();
            // } else {
            //     //get my projects and my child projects
            //     $projects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
            //         return $query->whereIn('role_id', $roles);
            //     })->paginate(12);
            // }
            $projects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
                return $query->whereIn('role_id', $roles);
            })->get();
            if ($projects) {
                return $this->success_response($projects, 200);
            } else {
                return $this->error_response("Not Found", 404);
            }
        }
        //if the user has created a project
        else if (auth()->user()->can('retrieve project') && auth()->user()->can('create project')) {
            //retrieve child roles
            $roles = $this->get_child_roles(auth()->user());
            $roles->push(auth()->user()->role_id);
            // return auth()->user()->projects;
            //   return $roles;

            //    if ($request->query("all") == "true") {
            //        //get my projects and my child projects
            //        $projects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
            //            return $query->whereIn('role_id', $roles);
            //        })->where('dept_id',auth()->user()->dept_id)->where('deleted_at',null)->get();
            //    } else {
            //        //get my projects and my child projects
            //        $projects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
            //            return $query->whereIn('role_id', $roles);
            //        })->where('dept_id',auth()->user()->dept_id)->where('deleted_at',null)->paginate(12);
            //    }
            $projects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
                return $query->whereIn('role_id', $roles);
            })->where('dept_id', auth()->user()->dept_id)->where('deleted_at', null)->get();
            if ($projects) {
                return $this->success_response($projects, 200);
            } else {
                return $this->error_response("Not Found", 404);
            }
        } else {
            // return 'hh';
            $payload = collect([]);
            auth()->user()->projects !== null ?
                $payload->push(...auth()->user()->projects->where('deleted_at', null)) : '';
                // dd($payload->where('id', auth()->user()->project->id));
            auth()->user()->project !== null && $payload->where('id', auth()->user()->project->id) ? $payload->push(auth()->user()->project->where('deleted_at', null)) : '';
            // $payload->paginate(12);
            return $this->success_response($payload, 200);
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

        if (auth()->user()->can('create project')) {
            //    dd($request->input('start_date'));
            $this->validate($request, [
                'name' => "required|min:3|string",
                'dept_id' => "required|numeric",
                'start_date' => "required|date",
                'end_date' => 'required|date|after_or_equal:start_date',

            ]);

            $project = new Project();
            $project = $project->fill($request->all());
            $project['created_by'] = auth()->user()->id;
            if ($project->save()) {
                return $this->success_response($project, 201);
            } else {
                return $this->error_response("Error in saving", 400);
            }
        } else {
            return $this->error_response("Forbidden!", 403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    { 
        // dd( $request->query);
        // return $request->query['all'];
        if ($request->query('all')) {
            $project = Project::where('id', $id)
                ->with('doc')
                ->with('department')
                ->with('human_resource')
                ->with('nonhuman_resource')
                ->with('tasks')
                ->first();
        } else {
            $project = Project::where('id', $id)
                ->with('human_resource')
                ->first();
        }

        if ($project) {
            return $this->success_response($project, 200);
        }
        return $this->error_response("Not Found!", 404);
    }

    /**
     * assign resources (human/nonhuman) to the specified project
     *
     *@param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return void
     */
    public function assign_resources(Request $request, $id)
    {


        if (auth()->user()->can('assign project')) {
            $this->validate($request, [
                'resources' => 'array'
            ]);
            $errorMesages = collect([]);
            $project = Project::find($id);
            $projectId = $project->id;
            // dd($taskId);
            if (!$project) {
                return $this->error_response("Not Found!", 404);
            }
            // $removeResources = collect([]);
            // $assignResources = collect([]);
            // dd(count($request->removeResources));
            if (count($request->resources) > 0) {
                //  $existingResources = ProjectResource::where('project_id',$projectId)->get();
                //  $newResources = collect($request->resources);
                //  foreach($existingResources as $existing){

                //      if($newResources->where('resource_id','!=',$existing->resource_id)->where('type','!=',$existing->type)){
                //         $assignResources->push($newResources->where('resource_id','!=',$existing->resource_id)->where('type','!=',$existing->type));

                //      }else{
                //         $existingResources->splice($existing,1);
                //      }



                //  }
                //  dd($assignResources);
                $humanResourcesCollection = collect($request->resources)->map(function ($item) use ($projectId, $errorMesages) {
                    $existingResource = ProjectResource::where('project_id', $projectId)
                        ->where('resource_id', $item["resource_id"])
                        ->where('type', $item["type"])
                        ->first();
                    if (!$existingResource) {
                        $resource = new ProjectResource();
                        $resource["resource_id"] = $item["resource_id"];
                        $resource["project_id"] = $projectId;
                        $resource["created_at"] =  date('Y-m-d H:i:s');
                        $resource["updated_at"] =  date('Y-m-d H:i:s');
                        $resource["type"] =  $item["type"];
                        return $resource->save();
                        // return $errorMesages->push([$existingResource . 'already exist']);
                        // return  $errorMesages[] = ;
                    }
                });
            }
            if (count($request->removeResources) > 0) {
                foreach ($request->removeResources as $resource) {
                    // dd($resource);
                    ProjectResource::where('project_id', $id)->where('resource_id', $resource["resource_id"])->where('type', $resource["type"])->delete();
                }
            }
            $project->doc;
            $project->department;
            $project->human_resource;
            $project->nonhuman_resource;
            $project->tasks;
            return $this->success_response($project, 200);

            // if (count($errorMesages) > 0) {
            //     return $this->error_response($errorMesages, 400);
            // } 
            // else {
            // }
            // }
        } else {
            return $this->error_response("Forbidden", 403);
        }
    }


    //getting the resource related data
    public function initial_resource()
    {
        //replace create project with assign project
        if (auth()->user()->can('create project')) {
            $roles = collect($this->get_child_roles(auth()->user()));
            $roles->push(auth()->user()->role_id);
            $users = User::whereIn('role_id', $roles)->with('role')->get();
            // dd($users);
            $nonHumanResouces = NonHumanResources::all();
            $resourceType = DbVariablesDetail::variableType('resource_type')->get(['id', 'value']);
            return $this->success_response(['humanResource' => $users, 'nonHumanResource' => $nonHumanResouces, 'resourceType' => $resourceType], 200);
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

        if (auth()->user()->can('edit project')) {
            $project = Project::find($id);
            if (!$project) {
                return $this->error_response("Not found", 404);
            }
            $this->validate($request, [
                'name' => "min:3|string",
                'dept_id' => "numeric",
                'start_date' => "date",
                'end_date' => 'date'
            ]);
            $updatedProject = $project->fill($request->all());
            if ($updatedProject->save()) {
                return $this->success_response($updatedProject, 200);
            } else {
                return $this->error_response("Error in updating", 400);
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
    public function destroy($id)
    {

        if (auth()->user()->can('delete project')) {
            $project = Project::find($id);
            if (!$project) {
                return $this->error_response("Not found", 404);
            }

            if ($project->delete()) {
                return $this->success_response($project, 200);
            } else {
                return $this->error_response("Error in deleting", 400);
            }
        } else {
            return $this->error_response("Forbidden!", 403);
        }
    }

    /**
     * calculate cost of specified project
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return void
     */
    public function cost(Request $request, $id)
    {

        $tasks = Project::find($id)->with('tasks')->first()->tasks;
        $overallEstimatedCost = 0;
        $overallTotalCost = 0;
        $costExceed = 0;
        $taskDetails = collect([]);
        foreach ($tasks as $task) {
            $task->team;
            $taskDetail = [];
            foreach ($task->team as $resource) {
                // dd($resource->id);

                $resourceDetail = $resource["detail"];
                $detail = Employee::where('user_id', $resource->id)->first();
                $workingDays = (int)DbVariablesDetail::variableType('working_days')->first()->value;
                $salaryPerHr = $detail["salary"] / $detail["working_hrs"] / $workingDays;
                $estimatedCost = $salaryPerHr * $resourceDetail["estimated_effort"];
                $totalCost = $resourceDetail["total_effort"] != null ? $salaryPerHr * $resourceDetail["total_effort"] : 0;
                $overallEstimatedCost += $estimatedCost;
                $overallTotalCost += $totalCost;
                $costExceed += $totalCost > $estimatedCost ? abs($totalCost - $estimatedCost) : 0;
                $taskDetail[] = [
                    [
                        "resource_id" => $resourceDetail["resource_id"],
                        "estimatedCost" => $estimatedCost,
                        "totalCost" => $totalCost,
                        "costExceed" => $totalCost > $estimatedCost ? abs($totalCost - $estimatedCost) : 0
                    ]
                ];
                // dd($salaryPerHr * $resourceDetail["estimated_effort"]);
            }
            $taskDetails[] = [
                $task->id => $taskDetail
            ];
            $taskDetail = [];
        }
        // dd($overallTotalCost,$overallEstimatedCost,$costExceed);
        return $this->success_response([
            "estimatedCost" => $overallEstimatedCost,
            "totalCost" => $overallTotalCost, "exceededCost" => $costExceed,
            "taskDetails" => $taskDetails
        ], 200);
    }
}
