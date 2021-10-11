<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ReusableTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Employee;
use DateTime;
use Exception;

class EmployeeController extends Controller
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
            if (auth()->user()->can("retrieve user")) {
                $employees = Employee::all();
                if ($employees) {
                    return $this->ok_response($employees, 200);
                } else {
                }
            } else {
                return $this->error_response("Unauthorized!", 401);
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
            if (auth()->user()->can("create user")) {
                $this->validate($request, [
                    'joining_date' => 'required',
                    'salary' => ' required',
                    'duty_start' => ' required',
                    'duty_end' => ' required|after:start_date',
                    'user_id' => 'required'
                ]);
    
                $startTime = strtotime($request->duty_start);
                $endTime = strtotime($request->duty_end);

                $employee = new Employee();
                $employee["joining_date"] = $request->joining_date;
                $employee["designation"] = $request->designation ? $request->designation : null;
                $employee["duty_start"] = date("h:i:s", strtotime($request->duty_start));
                $employee["duty_end"] = date("h:i:s", strtotime($request->duty_end));
                $employee["working_hrs"] = abs(($endTime - $startTime) / 60 / 60);
                $employee["created_by"] = auth()->user()->id;
                $employee["user_id"] = $request->user_id;
                $employee["salary"] = $request->salary;
                
                if ($employee->save()) {
                    return $this->ok_response($employee, 201);
                } else {
                    return $this->error_response("Error in creating new employee", 400);
                }
            } else {
                return $this->error_response("Unauthorized!", 401);
            }
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
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
