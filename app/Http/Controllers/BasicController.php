<?php

namespace App\Http\Controllers;

use App\Models\DbVariablesDetail;
use Exception;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;
use App\Models\DbVariables;
use App\Models\TagStatus;

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
            return $this->success_response($variables,200);
      
    }
    
    /**
     * get statuses specified to the tag
     *
     * @param  int $id (tagId)
     *@return \Illuminate\Http\Response
     */
    public function get_status($id){
        
            $statuses = TagStatus::where('tag_id',$id)->with('variable_detail:value,id')
            ->get(["id","status_id"]);
            if(!$statuses){
                return $this->error_response("Not found", 404);
    
            }
            return $this->success_response($statuses,200);
      
     
    }
}
