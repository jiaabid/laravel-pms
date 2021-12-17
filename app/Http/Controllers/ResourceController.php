<?php

namespace App\Http\Controllers;

use App\Models\NonHumanResources;
use Exception;
use Illuminate\Http\Request;
use App\Http\Traits\ResponseTrait;
class ResourceController extends Controller
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
        
            if (auth()->user()->can('retrieve department')) {
                $resources = NonHumanResources::where('deleted_at',NULL)->get();
               
                return $this->success_response($resources, 200);
                
                
            } else {
                return $this->error_response( "Forbidden!", 403);

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

      
            $this->validate($request, [
                'name' => 'required'
            ]);
            $resource = new NonHumanResources();
            $resource->fill($request->all());
            $resource['created_by'] = auth()->user()->id;
            $resource->save();
            return $this->success_response($resource, 201);

           
      
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        
            if (auth()->user()->can('retrieve department')) {
                $resource = NonHumanResources::find($id);
                if ($resource) {
                    return $this->success_response($resource, 200);

                  
                }
                return $this->error_response( "Not Found", 404);

            } else {
                return $this->error_response( "Forbidden!", 403);

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
        
            // if (auth()->user()->can('edit department')) {
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
            // } else {
            //     return $this->error_response( "Forbidden!", 403);

            // }
     
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       
            if (auth()->user()->can('delete department')) {
                $resource = NonHumanResources::find($id);
                if (!$resource) {
                    return $this->error_response("Not found",404);

                }

                if ($resource->delete()) {
                    return $this->success_response( $resource, 200);

                } else {
                    return $this->error_response( "Error in deleting", 400);

                }
            } else {
                return $this->error_response( "Forbidden!", 403);

            }
      
    }
}
