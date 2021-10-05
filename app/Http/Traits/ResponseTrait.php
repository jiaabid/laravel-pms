<?php

namespace App\Http\Traits;


trait ResponseTrait
{
    public function ok_response($success, $payload, $code)
    {
        return response()->json([
            "success" => $success,
            "payload" => $payload
        ], $code);
    }

    public function error_response($success,$msg,$code){
        return response()->json([
            "success" => $success,
            "error" => $msg
        ], $code);
    }
}
