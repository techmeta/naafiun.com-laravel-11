<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiHandler extends Exception
{
    private $customException;
    private int $exceptionCode;

    public function __construct($customException, int $exceptionCode = 0)
    {
        parent::__construct($customException, $exceptionCode);
        $this->customException = $customException;
        $this->exceptionCode = $exceptionCode;
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {

        return response()->json([
            'status' => false,
            'message' => $this->customException->getMessage(),

        ], $this->exceptionCode);
    }
}
