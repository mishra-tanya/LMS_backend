<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success(string $message, $data = null, int $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function error(string $message, $data = null, int $code = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
