<?php

namespace App\Domains\ApiResponse\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class AuthRegisterRequest.
 */
class AuthRegisterRequest extends FormRequest
{


  public function authorize()
  {
    return false;
  }

  public function rules()
  {
    $rules = [
      'otp' => 'required|string|min:4|max:4',
      'phone' => 'nullable|string|min:10|max:20',
      'email' => 'nullable|email',
    ];
    return $rules;
  }



  protected function failedValidation(Validator $validator)
  {
    $errors = (new ValidationException($validator))->errors();
    throw new HttpResponseException(
      response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
    );
  }
}
