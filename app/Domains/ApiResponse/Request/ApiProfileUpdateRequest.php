<?php

namespace App\Domains\ApiResponse\Request;

use App\Domains\Auth\Models\User;
use App\Rules\InExistsRule;
use App\Rules\OldPasswordRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ApiProfileUpdateRequest.
 */
class ApiProfileUpdateRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $auth_id = auth('sanctum')->id();
        $email = $this->email;
        $this->user = User::query()
            ->where('id', $auth_id)
            ->where('email', $email)
            ->first();

        return [
            'name' => ['required', 'string', 'min:4', 'max:55'],
            'email' => ['required', 'string', 'email', new InExistsRule($this->user)],
            'phone' => ['required', 'string', 'max:16'],
            'old_password' => ['required_with:password', 'nullable', 'string', 'min:6', 'max:32', new OldPasswordRule($this->user)],
            'password' => ['required_with:old_password', 'nullable', 'string', 'min:6', 'max:32', 'confirmed'],
        ];
    }


}
