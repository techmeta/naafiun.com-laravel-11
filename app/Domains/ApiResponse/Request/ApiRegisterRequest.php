<?php

namespace App\Domains\ApiResponse\Request;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Class ApiRegisterRequest.
 */
class ApiRegisterRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'phone' => 'required|string|max:25|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'confirm_password' => 'min:6|required_with:password|same:password'
        ];
    }

}
