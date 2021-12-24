<?php

namespace App\Http\Controllers;

use App\Models\DbVariablesDetail;
use Exception;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;
use App\Models\DbVariables;
use App\Models\TagStatus;
use App\Models\Project;
use App\Models\User;
use App\Http\Traits\ReusableTrait;
use App\Models\HResourcesTask;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class BasicController extends Controller
{
    use ReusableTrait, ResponseTrait;


    /**
     * get values of specified variable type from database
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function get_variable_values(Request $request, $id)
    {

        $values =  DbVariablesDetail::where('variable_id', $id)->get();
        if (!$values) {
            return $this->error_response("Not found", 404);
        }
        return $this->success_response($values, 200);
    }

    //get the database variables    
    /**
     * get variable types from database e.g:(task_status,user_type)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_variables(Request $request)
    {

        $variables = DbVariables::all();
        if (!$variables) {
            return $this->error_response("Not found", 404);
        }
        return $this->success_response($variables, 200);
    }

    //get the database variables detail   
    /**
     * get variable types from database e.g:(task_status,user_type)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function details()
    {

        $variables = DbVariablesDetail::all();

        if (!$variables) {
            return $this->error_response("Not found", 404);
        }
        return $this->success_response($variables, 200);
    }

    /**
     * get statuses specified to the tag
     *
     * @param  int $id (tagId)
     *@return \Illuminate\Http\Response
     */
    public function get_status($id)
    {

        $statuses = TagStatus::where('tag_id', $id)->with('variable_detail:value,id')
            ->get(["id", "status_id"]);
        if (!$statuses) {
            return $this->error_response("Not found", 404);
        }
        return $this->success_response($statuses, 200);
    }

    public function get_task_stats()
    {
        if (auth()->user()->admin) {
            $tasks = Task::where('deleted_at', null)->where('type', 18)
                ->select(DB::raw('status,count(*) as count'))
                ->groupBy('status')
                ->get();

            return $this->success_response($tasks, 200);
        } else {
            //assign by me tasks
            // dd('hello');
            //   $assignBy=  Task::where('created_by', auth()->user()->id)->where('type', 18)->where('deleted_at', null)->select(DB::raw('status,count(*) as count'))->groupBy('status')->get();
            $tasks = HResourcesTask::where('resource_id', auth()->user()->id)->where('deleted_at',null)->select(DB::raw('status,count(*) as count'))->groupBy('status')->get();
           
            return $this->success_response($tasks, 200);
        }
    }

    public function get_project_stats()
    {
        // dd('fsfsf');
        // return auth()->user()->can('create project');
        if ((auth()->user()->can('retrieve project') && auth()->user()->admin)) {

            //retrieve child roles
            $roles = $this->get_child_roles(auth()->user());
            $roles->push(auth()->user()->role_id);

            //completed projects
            $project  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
                return $query->whereIn('role_id', $roles);
            })->select(DB::raw('status,count(*) as count'))
                ->groupBy('status')->where('deleted_at', null)->get();

            //pending
            // $pendingProjects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
            //     return $query->whereIn('role_id', $roles);
            // })->where('status', 5)->where('deleted_at', null)->count();

            //late
            $lateProjects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
                return $query->whereIn('role_id', $roles);
            })->where('late', true)->where('deleted_at', null)->count();
            return $this->success_response(["status"=>$project,"late"=> $lateProjects], 200);
        }
        //if the user has created a project
        else if (auth()->user()->can('retrieve project') && auth()->user()->can('create project')) {
            //retrieve child roles
            $roles = $this->get_child_roles(auth()->user());
            $roles->push(auth()->user()->role_id);
            //completed projects
            $project  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
                return $query->whereIn('role_id', $roles);
            })->where('dept_id', auth()->user()->dept_id)->where('deleted_at', null)->select(DB::raw('status,count(*) as count'))
                ->groupBy('status')->where('deleted_at', null)->get();

            //pending
            // $pendingProjects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
            //     return $query->whereIn('role_id', $roles);
            // })->where('dept_id', auth()->user()->dept_id)->where('status', 5)->where('deleted_at', null)->count();

            //late
            $lateProjects  = Project::with('user')->whereHas('user', function ($query) use ($roles) {
                return $query->whereIn('role_id', $roles);
            })->where('dept_id', auth()->user()->dept_id)->where('late', true)->where('deleted_at', null)->count();
            return $this->success_response(["status"=>$project,"late"=> $lateProjects], 200);

        } else {


            $completedProjects=0;
            $pendingProjects=0;
            $lateProjects = 0;
           
            //  auth()->user()->projects->groupBy('status')->where('deleted_at', null)->get(['id']);
                $lateProjects = auth()->user()->projects->where('deleted_at', null)->where('late',0)->count();
            foreach (auth()->user()->projects->where('deleted_at', null) as $project) {
                if ($project->status == 6) {
                    $completedProjects++;
                } else if ($project->late) {
                    $lateProjects++;
                } else {
                    $pendingProjects++;
                }
            };
            $project = [
            [
                "status"=>5,
                "count"=>$pendingProjects
            ],
                [
                    "status"=>6,
                    "count"=>$completedProjects
                ]
                ];
            return $this->success_response(["status"=>$project,"late"=> $lateProjects], 200);

        }
    }
    public function get_user_stats()
    {
        //if user is super admin then it will get all the user created by hime
        if (auth()->user()->can('retrieve user') && auth()->user()->id == 1) {
            $users = User::where('created_by', auth()->user()->id)->with('role:id,name')->with('department:id,name')->get();

            if ($users) {
                return $this->success_response($users, 200);
            } else {
                return $this->error_response("No user exist!", 404);
            }
        }

        //get the child users (role hierarchy)
        else if (auth()->user()->can('retrieve user')) {

            //retrieve child roles  
            $roles = collect($this->get_child_roles(auth()->user()));
            $roles->push(auth()->user()->role_id);
            $childUsers = $this->get_child_users(auth()->user());
            // return $roles;
            // return $childUsers;
            // if ($request->query("all") == "true") {
            //     $users = auth()->user()->admin ? User::whereIn('role_id', $roles)->with('role:id,name')->with('department:id,name')->with('detail')->get() :
            //         User::whereIn('role_id', $roles)->where('dept_id', auth()->user()->dept_id)->with('role:id,name')->with('department:id,name')->with('detail')->get();
            // } else {
            //     // return auth()->user()->role_id;
            //     $users = auth()->user()->admin ? User::whereIn('role_id', $roles)->with('role:id,name')->with('department:id,name')->with('detail')->paginate(12) :
            //         User::whereIn('role_id', $roles)->where('dept_id', auth()->user()->dept_id)->with('role:id,name')->with('department:id,name')->with('detail')->paginate(12);
            //     // $users = User::whereIn('role_id', $roles)->whereIn('id', $childUsers)->with('role:id,name')->with('department:id,name')->with('detail')->paginate(12);
            // }
            $users = auth()->user()->admin ? User::whereIn('role_id', $roles)->with('role:id,name')->with('department:id,name')->with('detail')->get() :
                User::whereIn('role_id', $roles)->where('dept_id', auth()->user()->dept_id)->with('role:id,name')->with('department:id,name')->with('detail')->get();
            // $users = User::whereIn('role_id', $roles)->with('role:id,name')->with('department:id,name')->with('detail')->get();
            if ($users) {
                return $this->success_response($users, 200);
            } else {
                return $this->error_response("No user exist!", 404);
            }
        } else {
            return $this->error_response("Forbidden!", 403);
        }
    }
}
