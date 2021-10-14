<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use Exception;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;

class IssueController extends Controller
{
    use ResponseTrait;
 
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {

            $issueExist = Issue::find($id);
            if (!$issueExist) {
                return $this->error_response("Not found", 404);
            }
            return $this->success_response($issueExist, 200);
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }
    
    /**
     * change status of the specified issue in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     *  @return \Illuminate\Http\Response
     */
    public function change_status(Request $request, $id)
    {
        try {

            $this->validate($request, [
                "status" => 'required'
            ]);

            $issueExist = Issue::find($id);
            if (!$issueExist) {
                return $this->error_response("Not found", 404);
            }
            $issueExist["status"] = $request->status;
            if ($issueExist->save()) {
                return $this->success_response("status updated", 200);
            } else {
                return $this->error_response("error in status update", 400);
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
            $this->validate($request, [
                "name" => 'string',
                "description" => 'string',
                "approved" => 'boolean'
            ]);
            $issueExist = Issue::find($id);
            if (auth()->user()->id == $issueExist->created_by) {



                if (!$issueExist) {
                    return $this->error_response("Not found", 404);
                }
                $issueExist->fill($request->all());
                $issueExist["updated_by"] = auth()->user()->id;
                if ($issueExist->save()) {
                    return $this->success_response("issue updated", 200);
                } else {
                    return $this->error_response("error in status update", 400);
                }
            } else {
                return $this->error_response("Forbidden", 403);
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
            // if(auth()->user()->can()){

            $issueExist = Issue::find($id);
            if (auth()->user()->id == $issueExist->created_by) {
                if (!$issueExist) {
                    return $this->error_response('Not found', 404);
                }
                if ($issueExist->delete()) {
                    return $this->success_response("", 204);
                } else {
                    return $this->error_response("Error in delete", 400);
                }
            } else {
                return $this->error_response("Forbidden", 403);
            }

            // }else{

            // }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }
}
