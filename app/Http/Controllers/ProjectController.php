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
use App\Models\Employee;

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
    public function index()
    {
        try {
            if (auth()->user()->can('retrieve project')) {

                //retrieve child roles
                $roles = $this->get_child_roles(auth()->user());
                $roles->push(auth()->user()->id);

                //get my projects and my child projects
                $projects  = Project::whereHas('user', function ($query) use ($roles) {
                    return $query->whereIn('role_id', $roles);
                })->with('user')->get();

                if ($projects) {
                    return $this->success_response($projects, 200);
                } else {
                    return $this->error_response( "Not Found", 404);

                }
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

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
                return $this->error_response( "Error in saving", 400);

                    
                }
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

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
        try {
            if (auth()->user()->can('retrieve project')) {

                $projects = Project::find($id);
                $projects->doc;
                $projects->department;
                $projects->human_resource;
                $projects->nonhuman_resource;
                $projects->tasks;
                if ($projects) {
                    return $this->success_response($projects, 200);

                }
                return $this->error_response( "Not Found!", 404);

            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

        }
    }

    public function assign_resources(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'resources' => 'required|array'
            ]);
            $errorMesages = [];
            $project = Project::find($id);
            $projectId = $project->id;
            // dd($taskId);
            if (!$project) {
                return $this->error_response( "Not Found!", 404);
                
            }
            if ($request->resources) {

                $humanResourcesCollection = collect($request->resources)->map(function ($item) use ($projectId) {
                    $existingResource = ProjectResource::where('project_id', $projectId)
                        ->where('resource_id', $item["resource_id"])
                        ->where('type', $item["type"])
                        ->first();
                    if ($existingResource) {
                        return  $errorMesages[] = "already exist";
                    }
                    $resource = new ProjectResource();
                    $resource["resource_id"] = $item["resource_id"];
                    $resource["project_id"] = $projectId;
                    $resource["created_at"] =  date('Y-m-d H:i:s');
                    $resource["updated_at"] =  date('Y-m-d H:i:s');
                    $resource["type"] =  $item["type"];
                    return $resource->save();
                });

                if (count($errorMesages) > 0) {
                return $this->error_response( $errorMesages, 400);
                } else {
                    return $this->success_response( [
                        "msg" => "Resource Assigned!"
                    ], 200);
                }
            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

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
            if (auth()->user()->can('edit project')) {
                $project = Project::find($id);
                if (!$project) {
                    return $this->error_response("Not found",404);

                }
                $this->validate($request, [
                    'name' => "min:3|string",
                    'dept_id' => "numeric",
                    'start_date' => "date",
                    'end_date' => 'date'
                ]);
                $updatedProject = $project->fill($request->all());
                if ($updatedProject->save()) {
                    return $this->success_response( $updatedProject, 200);
                } else {
                    return $this->error_response( "Error in updating", 400);

                }
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

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

            if (auth()->user()->can('delete project')) {
                $project = Project::find($id);
                if (!$project) {
                    return $this->error_response( "Not found", 404);


                if ($project->delete()) {
                    return $this->success_response( [], 204);

                } else {
                    return $this->error_response( "Error in deleting", 400);

                }
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

        }
    }

    public function cost(Request $request, $id)
    {
        try {
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

                    $resourceDetail = $resource["pivot"];
                    $detail = Employee::where('user_id', $resource->id)->first();
                    // dd($detail);
                    $salaryPerHr = $detail["salary"] / $detail["working_hrs"] / 22;
                    $estimatedCost = $salaryPerHr * $resourceDetail["estimated_effort"];
                    $totalCost = $resourceDetail["total_effort"] != null ? $salaryPerHr * $resourceDetail["total_effort"] : 0;
                    $overallEstimatedCost += $estimatedCost;
                    $overallTotalCost += $totalCost;
                    $costExceed += $totalCost > $estimatedCost ? abs($totalCost - $estimatedCost) : 0;
                    $taskDetail[] = [
                        $resourceDetail->task_id => [
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
            return $this->ok_response([
                "estimatedCost" => $overallEstimatedCost,
                "totalCost" => $overallTotalCost, "exceededCost" => $costExceed,
                "taskDetails"=>$taskDetails
            ], 200);
        } catch (Exception $e) {
        }
    }
}

//for only my created projects
   // $project = Project::find($id);
                // if ($id !== null) {
                   
                // } else {
                //     $projects = auth()->user()->project;
                //     foreach ($projects as $project) {
                //         $project->doc;
                //     }
                // }