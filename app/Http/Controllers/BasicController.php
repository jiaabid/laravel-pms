<?php

namespace App\Http\Controllers;

use App\Models\DbVariablesDetail;
use Exception;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;
use App\Models\DbVariables;

class BasicController extends Controller
{
    use ResponseTrait;


    /**
     * get values of specified variable type from database
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function get_variable_values(Request $request, $id)
    {
        try {
            $values =  DbVariablesDetail::where('variable_id', $id)->get();
            if (!$values) {
                return $this->error_response("Not found", 404);
            }
            return $this->success_response($values, 200);
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
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
        try {
            $variables = DbVariables::all();
            if (!$variables) {
                return $this->error_response("Not found", 404);
            }
            return $this->success_response($variables, 200);
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }
}
