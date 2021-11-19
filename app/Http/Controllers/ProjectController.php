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

        if (auth()->user()->can('retrieve project')) {

            //retrieve child roles
            $roles = $this->get_child_roles(auth()->user());
            $roles->push(auth()->user()->role_id);


            if ($request->query("all") == "true") {
                //get my projects and my child projects
                $projects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
                    return $query->whereIn('role_id', $roles);
                })->get();
            } else {
                //get my projects and my child projects
                $projects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
                    return $query->whereIn('role_id', $roles);
                })->paginate(12);
            }
            if ($projects) {
                return $this->success_response($projects, 200);
            } else {
                return $this->error_response("Not Found", 404);
            }
        } else {
            $payload = collect([]);
            $payload->push(...auth()->user()->projects);
            $payload->push(...auth()->user()->project);
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
    public function show($id)
    {


        $projects = Project::where('id', $id)
            ->with('doc')
            ->with('department')
            ->with('human_resource')
            ->with('nonhuman_resource')
            ->with('tasks')
            ->first();
        if ($projects) {
            return $this->success_response($projects, 200);
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
                'resources' => 'required|array'
            ]);
            $errorMesages = collect([]);
            $project = Project::find($id);
            $projectId = $project->id;
            // dd($taskId);
            if (!$project) {
                return $this->error_response("Not Found!", 404);
            }
            if ($request->resources) {

                $humanResourcesCollection = collect($request->resources)->map(function ($item) use ($projectId, $errorMesages) {
                    $existingResource = ProjectResource::where('project_id', $projectId)
                        ->where('resource_id', $item["resource_id"])
                        ->where('type', $item["type"])
                        ->first();
                    if ($existingResource) {
                        return $errorMesages->push([$existingResource . 'already exist']);
                        // return  $errorMesages[] = ;
                    }
                    $resource = new ProjectResource();
                    $resource["resource_id"] = $item["resource_id"];
                    $resource["project_id"] = $projectId;
                    $resource["created_at"] =  date('Y-m-d H:i:s');
                    $resource["updated_at"] =  date('Y-m-d H:i:s');
                    $resource["type"] =  $item["type"];
                    return $resource->save();
                });
                $project->doc;
                $project->department;
                $project->human_resource;
                $project->nonhuman_resource;
                $project->tasks;

                if (count($errorMesages) > 0) {
                    return $this->error_response($errorMesages, 400);
                } else {
                    return $this->success_response( $project, 200);
                }
            }
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
