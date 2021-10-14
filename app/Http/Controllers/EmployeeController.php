<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\ReusableTrait;
use App\Http\Traits\ResponseTrait;
use App\Models\Employee;
use Carbon\Carbon;
use DateTime;
use Exception;

date_default_timezone_set('Asia/Karachi');

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
                    return $this->success_response($employees, 200);
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

                $startTime = Carbon::createFromTimeString($request->duty_start, 'Asia/Karachi');
                $endTime = Carbon::createFromTimeString($request->duty_end, 'Asia/Karachi');


                $employee = new Employee();
                $employee["joining_date"] = $request->joining_date;
                $employee["designation"] = $request->designation ? $request->designation : null;
                $employee["duty_start"] = $startTime->format("H:i:s");
                $employee["duty_end"] =  $endTime->format("H:i:s");
                $employee["working_hrs"] = abs((strtotime($endTime) - strtotime($startTime)) / 60 / 60);
                $employee["created_by"] = auth()->user()->id;
                $employee["user_id"] = $request->user_id;
                $employee["salary"] = $request->salary;

                if ($employee->save()) {
                    return $this->success_response($employee, 201);
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
        $employe = Employee::find($id);
        return $this->success_response($employe, 200);
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

            if (auth()->user()->can("edit user")) {

                $this->validate($request, [
                    'joining_date' => 'date',
                    'designation' => 'string',
                    'salary' => 'float',
                    'break' => 'boolean'

                ]);
                $startTime = "";
                $endTime = "";
                $employeeExist = Employee::find($id);
             
                if (!$employeeExist) {
                    return $this->error_response("Not Found", 404);
                }
                $employeeExist->fill($request->except('user_id', 'duty_start', 'duty_end'));
              

                if ($request->duty_start) {
                    $startTime = Carbon::createFromTimeString($request->duty_start, 'Asia/Karachi');
                    $employeeExist["duty_start"] = $startTime->format('H:i:s');
                 
                }
                if ($request->duty_end) {
                    $endTime = Carbon::createFromTimeString($request->duty_end, 'Asia/Karachi');
                    $employeeExist["duty_end"] = $endTime->format('H:i:s');
                }
                $startTime = strtotime($employeeExist["duty_start"]);
                $endTime = strtotime($employeeExist["duty_end"]);
                $employeeExist["working_hrs"] =  abs(($endTime - $startTime) / 60 / 60);
          
                if ($employeeExist->save()) {
                    return $this->success_response($employeeExist, 200);
                } else {
                    return $this->error_response("Bad Request", 400);
                }
            } else {
                return $this->error_response('Forbidden', 403);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }
}
