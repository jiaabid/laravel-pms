<?php

namespace App\Http\Controllers;

use App\Models\DbVariables;
use App\Models\Project;
use App\Models\ProjectResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProjectController extends Controller
{

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
                $projects = Project::all();
                if ($projects) {
                    return response()->json([
                        'status' => true,
                        'payload' => $projects
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'error' => 'No project exist!'
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
                    'end_date' => 'required|date',
                    'status' =>     'numeric'
                ]);

                $project = new Project();
                $project = $project->fill($request->all());
                $project['created_by'] = auth()->user()->id;
                if ($project->save()) {
                    return response()->json([
                        'payload' => $project,
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
                if ($projects) {
                    return response()->json([
                        "success" => true,
                        'payload' => $projects
                    ]);
                }
                return response()->json([
                    "success" => false,
                    "error" => "No such project exist!"
                ], 404);
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
                return response()->json([
                    "success" => false,
                    'error' => 'No such project exist!'
                ], 404);
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
                    return response()->json([
                        "success" => false,
                        'error' => 'No such department exist!'
                    ], 404);
                }
                $this->validate($request, [
                    'name' => "min:3|string",
                    'dept_id' => "numeric",
                    'start_date' => "date",
                    'end_date' => 'date'
                ]);
                $updatedProject = $project->fill($request->all());
                if ($updatedProject->save()) {
                    return response()->json([
                        "success" => true,
                        'payload' => $updatedProject
                    ]);
                } else {
                    return response()->json([
                        "success" => false,
                        'error' => 'Error in update'
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
                    return response()->json([
                        "success" => false,
                        'error' => 'No such project exist!'
                    ], 404);
                }

                if ($project->delete()) {
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
            echo $e;
            return response()->json([
                'error' => $e
            ], 500);
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