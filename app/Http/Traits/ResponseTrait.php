<?php

namespace App\Http\Traits;


trait ResponseTrait
{
    /**
     * shorthand for success response
     *
     * @param  mixed $payload
     * @param  int $code
     * @return response
     */
    public function success_response($payload, $code)
    {
        return response()->json([
            "success" => true,
            "payload" => $payload
        ], $code);
    }

    /**
     * shorthand for error response
     *
     * @param  mixed $payload
     * @param  int $code
     * @return response
     */
    public function error_response($msg, $code)
    {
        return response()->json([
            "success" => false,
            "message"=>$msg,
            "errors" => [
                "name" => [$msg]
            ]
        ], $code);
    }
}
