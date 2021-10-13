<?php

namespace App\Http\Controllers;

use App\Models\NhResourcesTask;
use App\Models\NonHumanResources;
use Exception;
use Illuminate\Http\Request;

class ResourceController extends Controller
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
                $resources = NonHumanResources::all();
                // foreach ($departs as $depart) {
                //     $depart->user;
                // }
                return $this->success_response($resources, 200);
                
                
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

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
            // if (auth()->user()->can('create resourcement')) {
            $this->validate($request, [
                'name' => 'required'
            ]);
            $resource = new NonHumanResources();
            $resource->fill($request->all());
            $resource['created_by'] = auth()->user()->id;
            $resource->save();
            return $this->success_response($resource, 201);

            // } else {
            //     return response()->json([
            //         'success' => false,
            //         'payload' => "Unauthorized!"
            //     ], 401);
            // }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

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

        try {
            if (auth()->user()->can('retrieve department')) {
                $resource = NonHumanResources::find($id);
                if ($resource) {
                    return $this->success_response($resource, 200);

                  
                }
                return $this->error_response( "Not Found", 404);

            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

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
                $resource = NonHumanResources::find($id);
                if (!$resource) {
                    return $this->error_response( "Not Found", 404);

                }
                $updatedResource = $resource->fill($request->all());
                $updatedResource["updated_by"] = auth()->user()->id;
                if ($updatedResource->save()) {
                    return $this->success_response($updatedResource, 200);

                } else {
                    return $this->error_response( "Error in updating", 400);

                }
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

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
                $resource = NonHumanResources::find($id);
                if (!$resource) {
                    return $this->error_response("Not found",404);

                }

                if ($resource->delete()) {
                    return $this->success_response( [], 204);

                } else {
                    return $this->error_response( "Error in deleting", 400);

                }
            } else {
                return $this->error_response( "Unauthorized!", 401);

            }
        } catch (Exception $e) {
            return $this->error_response( $e->getMessage(), 500);

        }
    }
}
