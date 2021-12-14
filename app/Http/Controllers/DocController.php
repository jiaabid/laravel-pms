<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use App\Models\Project;
use App\Models\ProjectDocs;
use Exception;
use File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\ResponseTrait;

class DocController extends Controller
{
    use ResponseTrait;
    // public function __construct()
    // {
    //     $this->middleware(['auth']);
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        //retrieve all the documents created by the user

        if (auth()->user()->can('retrieve project')) {
            //  dd(auth()->user()->project);
            $projects = auth()->user()->project;
            $payload = [];
            foreach ($projects as $project) {
                $project->doc;
            }
            // dd($payload);
            return $this->success_response($projects, 200);
        } else {
            return $this->error_response("Forbidden!", 403);
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
            if (auth()->user()->can('create project')) {
                $this->validate($request, [
                    'name' => 'required'
                ]);
                $file = $request->file('file');
                $project = Project::where('id', $request->projectId)->first();
                $filename = 'public/files/' . $project->name;
              

                $path = $file->store($filename);
                $name = $file->getClientOriginalName();
                $filepath = explode('/',$path);
                try {
                    $doc = new Doc();
                    $doc['name'] = $request->name;
                    $doc['description'] = $request->description ? $request->description : null;
                    $doc['link'] = $filepath[count($filepath)-2].'/'.$filepath[count($filepath)-1   ];
                    $doc['created_by'] = auth()->user()->id;
                } catch (Exception $e) {
                    return $this->error_response($e->getMessage(), 500);
                }
                DB::beginTransaction();
                $saved = $doc->save();
                // dd($doc);
                if ($saved) {
                    $res = $this->insert_into_bridge_table($request->projectId, $doc->id);
                    DB::commit();
                    $projects = Project::where('id', $request->projectId)
                        ->with('doc')
                        ->with('department')
                        ->with('human_resource')
                        ->with('nonhuman_resource')
                        ->with('tasks')
                        ->first();
                    if ($res) {
                        return $this->success_response($projects, 201);
                    } else {
                        return $this->error_response('error in uploading!', 400);
                    }
                } else {
                    return $this->error_response('error in uploading!', 400);
                }
            } else {
                return $this->error_response("Forbidden!", 403);
            }
        } catch (Exception $e) {
            return $this->error_response($e->getMessage(), 500);;
        }
    }

    /**
     * Insert the projectid and docid in bridge tabel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    function insert_into_bridge_table($projectId, $docId)
    {
        $item = new ProjectDocs();
        $item['project_id'] = $projectId;
        $item['doc_id'] = $docId;
        return $item->save();
    }



    /**
     * Display the specified resource.
     * return the file contents
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $docIds = ProjectDocs::where('project_id', $id)->get()->pluck('doc_id');
        // return $docIds;
        $exist = Doc::whereIn('id', $docIds)->get();
        if ($exist) {
            // $content = Storage::get($exist->link);
            // dd($content);
            return $this->success_response($exist, 200);
        } else {
            return $this->error_response("Not found", 404);
        }
    }
    public function viewFile($id)
    {
        $exist = Doc::where('id', $id)->first();
        if ($exist) {
            $content = Storage::get($exist->link);
            // dd($content);
            return $this->success_response(utf8_encode($content), 200);
        } else {
            return $this->error_response("Not found", 404);
        }
    }

    /**
     * download the file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download_file(Request $request, $id)
    {

        // if (auth()->user()->can('create project')) {
            $exist = Doc::find($id);
            // dd($exist);
            $path = $exist->link;
            if ($exist) {
                // return Storage::download($exist->link);
                return response()->download(storage_path("app/" . $path));
            } else {
                return $this->error_response("Not found", 404);
            }
        // } else {
        //     return $this->error_response("Forbidden!", 403);
        // }
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

        if (auth()->user()->can('edit project')) {
            $doc = Doc::find($id);
            if (!$doc) {
                return $this->error_response("Not found", 404);
            }
            $updatedDoc = $doc->fill($request->all());
            $updatedDoc["updated_by"] = auth()->user()->id;
            if ($updatedDoc->save()) {
                return response()->json([
                    "success" => true,
                    'payload' => $doc
                ]);
            } else {
                return $this->error_response("Error in updating", 400);
            }
        } else {
            return $this->error_response("Forbidden!", 403);
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


        if (auth()->user()->can('delete project')) {
            $doc = Doc::find($id);
            if (!$doc) {
                return $this->error_response("Not found", 404);
            }

            if ($doc->delete()) {
                return $this->success_response($doc, 200);
            } else {
                return $this->error_response("Error in deleting", 400);
            }
        } else {
            return $this->error_response("Forbidden!", 403);
        }
    }
}
