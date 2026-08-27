<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse as JsonResponseAlias;

trait ApiResponse
{
    /**
     * Send a success response
     *
     * @param string|array $data
     * @param string $message
     * @param int $status
     * @return JsonResponseAlias
     */
    public function successResponse($data = [], $message = 'Success', $status = 200): JsonResponseAlias
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Send an error response
     *
     * @param string|array $errors
     * @param string $message
     * @param int $status
     * @return JsonResponseAlias
     */
    public function errorResponse($errors = [], $message = 'Error', $status = 422): JsonResponseAlias
    {
        if ($errors instanceof \Throwable) {
            $exception = $errors;
            $errors = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
            if (config('app.debug')) {
                $errors['trace'] = $exception->getTraceAsString();
            }
        }

        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
