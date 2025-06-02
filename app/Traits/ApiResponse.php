<?php

namespace App\Traits;

use App\Enums\ResponseCode;

trait ApiResponse
{
    protected function successResponse($data = null, ResponseCode $code = ResponseCode::SUCCESS, int $status = 200)
    {
        return response()->json([
            'status' => $status,
            'code' => $code->value,
            'message' => $code->value,
            'data' => $data,
        ], $status);
    }

    protected function errorResponse(ResponseCode $code = ResponseCode::SERVER_ERROR, int $status = 500, $errors = null)
    {
        return response()->json([
            'status' => $status,
            'code' => $code->value,
            'message' => $code->value,
            'errors' => $errors,
        ], $status);
    }

    protected function validationErrorResponse($errors, ResponseCode $code = ResponseCode::VALIDATION_ERROR, int $status = 422)
    {
        return $this->errorResponse($code, $status, $errors);
    }
}
