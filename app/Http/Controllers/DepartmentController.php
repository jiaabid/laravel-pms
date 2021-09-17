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
        $departs = Department::all();
        return response()->json([
            "success" => true,
            'payload' => $departs
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
          
        ]);

        try {
            $depart = Department::create([
                'name' => $request->name,
                'description' => $request->description
            ]);
            return response()->json([
                "success" => true,
                'payload' => $depart
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'error' => $e
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
        $depart = Department::find($id);
        if ($depart) {
            return response()->json([
                "success" => true,
                'payload' => $depart
            ]);
        }
        return response()->json([
            "success"=>false,
            "error" => "No such department exist!"
        ], 404);
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
        $depart = Department::find($id);
        if (!$depart) {
            return response()->json([
                "success"=>false,
                'error' => 'No such department exist!'
            ], 404);
        }
        $updatedDepart = $depart->fill($request->all());
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
        } catch (Exception $e) {
            echo $e;
            return response()->json([
                'error' => $e
            ], 500);
        }
    }
}
