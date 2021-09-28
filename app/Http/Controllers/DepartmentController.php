<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class DepartmentController extends Controller
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
            if (auth()->user()->can('retrieve department')) {
                $departs = Department::all();
                foreach ($departs as $depart) {
                    $depart->user;
                }
                return response()->json([
                    "success" => true,
                    'payload' => $departs
                ]);
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
            if (auth()->user()->can('create department')) {
                $this->validate($request, [
                    'name' => 'required'
                ]);
                $depart = new Department();
                $depart->fill($request->all());
                $depart['created_by'] = auth()->user()->id;
                // $depart = Department::create([
                //     'name' => $request->name,
                //     'description' => $request->description,
                //     'created_by' => auth()->user()->id
                // ]);
                $depart->save();
                return response()->json([
                    "success" => true,
                    'payload' => $depart
                ], 201);
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
                    return response()->json([
                        "success" => true,
                        'payload' => $depart
                    ]);
                }
                return response()->json([
                    "success" => false,
                    "error" => "No such department exist!"
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
                    return response()->json([
                        "success" => false,
                        'error' => 'No such department exist!'
                    ], 404);
                }
                $updatedDepart = $depart->fill($request->all());
                $updatedDepart["updated_by"] = auth()->user()->id;
                if ($updatedDepart->save()) {
                    return response()->json([
                        "success" => true,
                        'payload' => $depart
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

            if (auth()->user()->can('delete department')) {
                $depart = Department::find($id);
                if (!$depart) {
                    return response()->json([
                        "success" => false,
                        'error' => 'No such department exist!'
                    ], 404);
                }

                if ($depart->delete()) {
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
