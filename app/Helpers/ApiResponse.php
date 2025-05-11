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
        $errorType = ($code >= 500) ? 'server_error' : 'client_error';
        
        return response()->json([
            'status' => false,
            'error_type' => $errorType,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function clientError(string $message, $data = null, int $code = 400)
    {
        return self::error($message, $data, $code);
    }

    public static function serverError(string $message, $data = null, int $code = 500)
    {
        return self::error($message, $data, $code);
    }
}
