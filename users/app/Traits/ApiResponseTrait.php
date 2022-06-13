<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait ApiResponseTrait
{
    /**
     * @param mixed $data
     * @param int $customCode
     * @return JsonResponse
     */

    public function successResponse(mixed $data, int $customCode = ResponseAlias::HTTP_OK) : JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'data' => $data,
            'code' => $customCode
        ]);
    }

    /**
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */

    public function errorResponse(mixed $data, int $statusCode = ResponseAlias::HTTP_UNPROCESSABLE_ENTITY) : JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'data' => $data,
        ], $statusCode);
    }
}
