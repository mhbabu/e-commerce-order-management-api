<?php

if (!function_exists('jsonResponse')) {
    function jsonResponse(string $message, bool $status, $data = null, int $statusCode = 200)
    {
        return response()->json([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ], $statusCode);
    }
}

/**
 * Return a standardized JSON response with pagination data.
 *
 * @param string $message
 * @param bool $status
 * @param array $response
 * @param int $statusCode
 * @return \Illuminate\Http\JsonResponse
 */
if (!function_exists('jsonResponseWithPagination')) {
    function jsonResponseWithPagination(string $message, bool $status, $response, int $statusCode = 200)
    {
        return response()->json(['message' => $message, 'status' => $status] + $response, $statusCode);
    }
}