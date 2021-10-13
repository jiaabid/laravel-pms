<?php

namespace App\Http\Traits;


trait ResponseTrait
{
    public function success_response( $payload, $code)
    {
        return response()->json([
            "success" => true,
            "payload" => $payload
        ], $code);
    }

    public function error_response($msg,$code){
        return response()->json([
            "success" =>false,
            "error" => $msg
        ], $code);
    }
}
