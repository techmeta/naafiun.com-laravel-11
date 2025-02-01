<?php

namespace App\Traits;

use App\Exceptions\ApiHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

trait ApiResponse
{
    /**
     * Send any success response
     *
     * @param null $data
     * @param string $message
     * @param integer $statusCode
     *
     * @return JsonResponse
     * @author MD Shariful Islam
     */
    public function success($data = null, string $message = "Operation Successful!", int $statusCode = ResponseAlias::HTTP_OK): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => true,
            'code' => $statusCode,
            'data' => $data
        ], $statusCode);
    }

    /**
     * @throws ApiHandler
     */
    public function exceptionError(?Throwable $exception = null, $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, $showException = true): JsonResponse
    {
        Log::error($exception->getFile() . '::' . $exception->getLine() . ' :: ' . $exception->getMessage());

        if ($showException) {

            throw new ApiHandler($exception, $statusCode);

        } else {

            return response()->json([
                'message' => 'Sorry! Something went wrong',
                'status' => false,
                'code' => $statusCode,
                'data' => []
            ], $statusCode);
        }
    }

    /**
     * Send any error response
     *
     * @param null $data
     * @param string $message
     * @param integer $statusCode
     * @return JsonResponse
     */
    public function error($data = Null, string $message = "Operation Failed!", int $statusCode = Response::HTTP_EXPECTATION_FAILED): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => false,
            'code' => $statusCode,
            'data' => $data
        ], $statusCode);
    }

    /**
     * @throws ApiHandler
     */
    public function exceptionErrorString($errorMessage = '', $errorFile = '', $errorLine = '', $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, $showException = true): JsonResponse
    {
        Log::error($errorFile . '::' . $errorLine . ' :: ' . $errorMessage);

        if ($showException) {

            throw new ApiHandler($errorMessage, $statusCode);

        } else {

            return response()->json([
                'message' => 'Sorry! Something went wrong',
                'status' => false,
                'code' => $statusCode,
                'data' => []
            ], $statusCode);
        }
    }

    public function badRequest(string $message = 'Invalid request', $statusCode = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => false,
            'code' => $statusCode
        ], $statusCode);
    }


    public function noData($statusCode = Response::HTTP_NO_CONTENT, string $message = 'Sorry! data not found.'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => false,
            'code' => $statusCode,
            'data' => []
        ], $statusCode);
    }

}
