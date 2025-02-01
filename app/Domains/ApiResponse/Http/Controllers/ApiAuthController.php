<?php

namespace App\Domains\ApiResponse\Http\Controllers;

use App\Domains\ApiResponse\Request\ApiAuthRequest;
use App\Domains\ApiResponse\Request\ApiProfileUpdateRequest;
use App\Domains\ApiResponse\Request\ApiRegisterRequest;
use App\Domains\ApiResponse\Service\ApiAuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiAuthController extends Controller
{

    public ApiAuthService $apiAuthService;

    public function __construct(ApiAuthService $apiAuthService)
    {
        $this->apiAuthService = $apiAuthService;
    }


    public function authUser(): JsonResponse
    {
        $data = $this->apiAuthService->authUser();
        return $this->success($data ?: ['data' => null], $data ? 'fetch data' : 'no auth', 200);
    }

    public function checkExistsCustomer(): JsonResponse
    {
        $data = $this->apiAuthService->checkExistsCustomer();
        $message = $data['message'] ?? '';
        $code = $data['code'] ?? '';
        $data = $data['data'] ?? '';
        return $this->success($data, $message, $code);
    }


    public function loginCustomer(ApiAuthRequest $request): JsonResponse
    {
        $data = $this->apiAuthService->loginCustomer($request);
        $message = $data['message'] ?? '';
        $code = $data['code'] ?? '';
        $data = $data['data'] ?? '';
        return $this->success($data, $message, $code);
    }

    /**
     * @throws \Throwable
     */
    public function registerCustomer(ApiRegisterRequest $request): JsonResponse
    {
        $data = $this->apiAuthService->registerUser($request);
        $message = $data['message'] ?? '';
        $code = $data['code'] ?? '';
        $data = $data['data'] ?? '';
        return $this->success($data, $message, $code);
    }

    public function OtpVerifyOtpCode(): JsonResponse
    {
        $data = $this->apiAuthService->OtpVerify();
        $message = $data['message'] ?? '';
        $code = $data['code'] ?? '';
        $data = $data['data'] ?? '';
        return $this->success($data, $message, $code);
    }

    public function resendOtpCode(): JsonResponse
    {
        $data = $this->apiAuthService->resend_otp();
        $message = $data['message'] ?? '';
        $code = $data['code'] ?? '';
        $data = $data['data'] ?? '';
        return $this->success($data, $message, $code);
    }

    public function updateProfile(ApiProfileUpdateRequest $request): JsonResponse
    {
        $data = $this->apiAuthService->updateProfile($request);
        $message = $data['message'] ?? '';
        $code = $data['code'] ?? 404;
        $data = $data['data'] ?? '';
        return $this->success($data, $message, $code);
    }

    public function forgotPassword(): JsonResponse
    {
        $validator = Validator::make(request()->all(), [
            'email' => ['required', 'string', 'email']
        ]);
        $email = request('email', null);

        if ($validator->fails() || !$email) {
            return $this->error($validator->errors(), 'Type your valid email', 422);
        }

        $data = $this->apiAuthService->forgotPassword();
        $message = $data['message'] ?? '';
        $code = $data['code'] ?? '';
        $data = $data['data'] ?? '';
        return $this->success($data, $message, $code);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'token' => 'required|string|min:6|max:255',
        ]);

        if ($validator->fails()) {
            return response(['status' => false, 'errors' => $validator->errors(), 'message' => 'Type your valid email'], 422);
        }
        $data = $this->apiAuthService->resetPassword();
        $message = $data['message'] ?? '';
        $code = $data['code'] ?? '';
        $data = $data['data'] ?? '';
        return $this->success($data, $message, $code);
    }

    public function logout(Request $request)
    {
        $user = auth('sanctum')->user();
        if ($user) {
            $user->tokens()->delete();
            return response([
                'status' => true,
                'msg' => 'Logged out successfully'
            ]);
        }

        return response([
            'status' => true,
            'msg' => 'Already logged out!'
        ]);
    }
}
