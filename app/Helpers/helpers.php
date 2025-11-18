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

if (!function_exists('formatDateTime')) {
    /**
     * Format a timestamp into a standardized string
     *
     * @param \DateTime|string|null $timestamp
     * @param string $format
     * @return string|null
     */
    function formatDateTime($timestamp, string $format = 'Y-m-d H:i:s'): ?string
    {
        if (!$timestamp) return null;

        if (is_string($timestamp)) {
            return date($format, strtotime($timestamp));
        }

        return $timestamp->format($format);
    }
}