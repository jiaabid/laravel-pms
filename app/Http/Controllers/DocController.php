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

class DocController extends Controller
{
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
        // dd($request);
        try {
            // dd($request->hasFile('check'));


            // if (auth()->user()->can('create project')) {
            // $this->validate($request, [
            //     'name' => 'required'
            // ]);
            $file = $request->file('file');

            $path = $file->store('public/images');
            $name = $file->getClientOriginalName();

            $doc = new Doc();
            $doc['name'] = $request->name;
            $doc['description'] = $request->description ? $request->description : null;
            $doc['link'] = $path;
            $doc['created_by'] = auth()->user()->id;
            DB::beginTransaction();
            $saved = $doc->save();
            // dd($doc);
            if ($saved) {
                $this->insert_into_bridge_table($request->id, $doc->id);
                DB::commit();
            } else {
            }

            // } else {
            // }
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    function insert_into_bridge_table($pId, $docId)
    {
        $item = new ProjectDocs();
        $item['project_id'] = $pId;
        $item['doc_id'] = $docId;
        if ($item->save()) {
            return response()->json([
                'status' => true,
                'payload' => [
                    "msg" => "doc uploaded"
                ]
            ]);
        } else {
            return response()->json([
                'status' => false,
                'error' => 'error in uploading!'
            ]);
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
            $exist = Doc::find($id);
            if ($exist) {
                $content = Storage::get($exist->link);
                return response()->json([
                    'status' => true,
                    'payload' => $content
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'No such file exist'
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function download_file(Request $request, $id)
    {
        try {
            if (auth()->user()->can('download file')) {
                $exist = Doc::find($id);
                $path = $exist->link;
                if ($exist) {
                    return Storage::download($exist->link);
                    // return response()->download(storage_path($path));
                } else {
                    return response()->json([
                        'success' => false,
                        'payload' => "Not Found"
                    ], 404);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'payload' => "Unauthorized!"
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
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
