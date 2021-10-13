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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
                return $this->ok_response("status updated", 200);
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
        try {
            // if(auth()->user()->can()){
            $issueExist = Issue::find($id);
            if (!$issueExist) {
                return $this->error_response('Not found', 404);
            }
            if ($issueExist->delete()) {
                return $this->ok_response("", 204);
            } else {
                return $this->error_response("Error in delete", 400);
            }
            // }else{

            // }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);
        }
    }
}
