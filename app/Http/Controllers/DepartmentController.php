<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Exception;
use Illuminate\Http\Request;
use Throwable;
use App\Http\Traits\ResponseTrait;

class DepartmentController extends Controller
{
    use ResponseTrait;
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
            if (auth()->user()->can('retrieve department')) {
                $departs = Department::all();
                foreach ($departs as $depart) {
                    $depart->user;
                }
                return $this->success_response($departs, 200);
            } else {
                return $this->error_response("Unauthorized!", 401);;
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
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
            if (auth()->user()->can('create department')) {
                $this->validate($request, [
                    'name' => 'required'
                ]);
                $depart = new Department();
                $depart->fill($request->all());
                $depart['created_by'] = auth()->user()->id;
                $depart->save();
                return $this->success_response($depart, 201);
            } else {
                return $this->error_response("Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }

        // $this->validate($request, [
        //     'name' => 'required',

        // ]);
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
            if (auth()->user()->can('retrieve department')) {
                $depart = Department::find($id);
                if ($depart) {
                    return $this->success_response($depart, 200);
                }
                return $this->error_response("No such department exist!", 404);
            } else {
                return $this->error_response("Unauthorized!", 401);
            }
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
            if (auth()->user()->can('edit department')) {
                $depart = Department::find($id);
                if (!$depart) {
                    return $this->error_response("Not found", 404);
                }
                $updatedDepart = $depart->fill($request->all());
                $updatedDepart["updated_by"] = auth()->user()->id;
                if ($updatedDepart->save()) {
                    return $this->success_response($depart, 200);
                } else {
                    return $this->error_response("Error in updating", 400);
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

            if (auth()->user()->can('delete department')) {
                $depart = Department::find($id);
                if (!$depart) {
                    return $this->error_response("Not found", 404);
                }

                if ($depart->delete()) {
                    return $this->success_response([], 204);
                } else {
                    return $this->error_response("Error in deleting", 400);
                }
            } else {
                return $this->error_response("Unauthorized!", 401);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }
}
