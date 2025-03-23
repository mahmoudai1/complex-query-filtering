<?php

namespace App;

trait ApiResponseTrait
{
    protected function responseHandler($status = true, $message = 'Success', $data = [], $statusCode = 200)
    {
        return response()->json([
            'success' => $status,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}
