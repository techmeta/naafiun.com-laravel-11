<?php

namespace App\Domains\ApiResponse\Service;

use App\Domains\ApiResponse\Resources\CustomerResource;
use App\Domains\Auth\Events\User\UserCreated;
use App\Domains\Auth\Events\User\UserLoggedIn;
use App\Domains\Auth\Events\User\UserRegisterOTP;
use App\Domains\Cart\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Ramsey\Uuid\Uuid;
use Throwable;

/**
 * Class ApiAuthService.
 */
class ApiAuthService
{


    public function authUser(): ?CustomerResource
    {
        $user_id = auth('sanctum')->id();
        $user = $user_id ? User::with('roles', 'permissions')->where('id', $user_id)->first() : null;
        $viewBackend = $user ? $user->can('view_backend') : false;

        return $user ? CustomerResource::single($user, ['view_backend' => $viewBackend, 'roles', 'permissions']) : null;
    }

    /**
     * @throws Throwable
     */
    private function createUser($register): ?User
    {
        $name = request('name');
        $phone = request('phone');
        $password = request('password');

        DB::beginTransaction();
        try {
            $user = new User();
            $user->type = 'user';
            $user->name = $name;
            $user->phone = $phone;
            $user->email = $register->email ?: "{$phone}@email.com";
            $user->email_verified_at = now();
            $user->password = Hash::make($password);
            $user->active = true;
            $user->save();
            $user->refresh();
            if ($user) {
                event(new UserCreated($user));
            }
            DB::commit();
            return $user;
        } catch (\Exception $exception) {
            DB::rollBack();
        }

        return null;
    }

    private function saveFirstAddress($user, $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $address = new Address();
            $address->fill($data);
            $address->user_id = $user->id;
            $address->name = 'Default Address';
            $address->save();
            return $address;
        });
    }

    public function checkExistsCustomer(): array
    {
        $email = request('email', '');
        if (!$email) {
            return [
                'code' => 422,
                'message' => "phone or email is not available",
                'data' => []
            ];
        }

        $otpCode = rand(100000, 999999);
        $user = User::query()->where('email', $email)->first();


        if ($user && $user->otp_verified_at) {
            return [
                'code' => 200,
                'message' => "auth checked!",
                'data' => [
                    "login" => true,
                    "email" => $email,
                ]
            ];
        }

        if ($user) {
            $user->email = $email;
            $user->otp_code = $otpCode;
            $user->otp_expired_at = now()->addMinutes(15);
            $user->save();
            $register = $user;
        } else {
            $register = new User();
            $register->phone = '';
            $register->email = $email;
            $register->otp_code = $otpCode;
            $register->otp_expired_at = now()->addMinutes(15);
            $register->save();
        }

        event(new UserRegisterOTP($register));

        return [
            'code' => 200,
            'message' => "OTP send to you Successfully!",
            'data' => [
                "uuid" => $user->id,
                "email" => $email,
            ]
        ];

    }

    public function loginCustomer($request): array
    {
        $is_login = $request->authenticate();

        $email = request('email');
        $phone = request('phone');

        if ($is_login) {
            $user = User::query()
                ->when($email, function ($query) use ($email) {
                    $query->where('email', $email);
                })
                ->when($phone, function ($query) use ($phone) {
                    $query->where('phone', $phone);
                })
                ->first();
            if ($user) {
                event(new UserLoggedIn($user));
                return [
                    'code' => 200,
                    'message' => "Login successfully!",
                    'data' => [
                        "token" => $user->createToken($user->email)->plainTextToken,
                        "user" => $user->only('id','active', 'name', 'phone', 'email', 'email_verified_at'),
                    ]
                ];
            }
        }

        return [
            'code' => 401,
            'message' => "Sorry! login failed",
            'data' => [
                "uuid" => '',
                "email" => '',
                "phone" => '',
            ]
        ];
    }

    /**
     * @throws Throwable
     */
    public function registerUser($request): array
    {
        $uuid = $request->uuid;
        $email = $request->email;
        $phone = $request->phone;

        $register = UserRegistration::query()
            ->where('status', 'checked')
            ->where('email', $email)
            ->where('uuid', $uuid)
            ->first();
        if (!$register) {
            $register = UserRegistration::query()
                ->where('status', 'checked')
                ->where('phone', $phone)
                ->where('uuid', $uuid)
                ->first();
        }


        if ($register) {
            $user = $this->createUser($register);
            if ($user) {
                event(new UserLoggedIn($user));

                $register->forceDelete();

                return [
                    'code' => 200,
                    'message' => "Register Successfully!",
                    'data' => [
                        "token" => $user->createToken($user->email)->plainTextToken,
                        "user" => $user->only('active', 'name', 'phone', 'email', 'email_verified_at'),
                    ]
                ];
            }
        }

        return [
            'code' => 422,
            'message' => "Sorry, No Data found!",
            'data' => [
                "uuid" => '',
                "email" => '',
                "phone" => '',
            ]
        ];
    }

    public function OtpVerify(): array
    {
        $otp_code = request('otp_code');
        $email = request('email');
        $uuid = request('uuid');

        $register = UserRegistration::query()
            ->where('email', $email)
            ->where('uuid', $uuid)
            ->where('otp_code', $otp_code)
            ->first();

        if (!$register) {
            return [
                'code' => 422,
                'message' => "Sorry, No Data found!",
                'data' => [
                    "uuid" => '',
                    "email" => '',
                    "phone" => '',
                ]
            ];
        }

        $register->status = 'checked';
        $register->save();
        $register->refresh();

        $user = User::query()->where('email', $email)->first();
        $token = null;
        if ($user) {
            $token = Password::createToken($user);
        }

        return [
            'code' => 200,
            'message' => "OTP send to you Successfully!",
            'data' => [
                "uuid" => $register->uuid,
                "email" => $email,
                "register" => true,
                "reset_token" => $token,
            ]
        ];
    }

    public function resend_otp(): array
    {
        $otpCode = randomOTPCode();
        $phone = request('phone');
        $email = request('email');
        $uuid = request('uuid');

        $register = UserRegistration::query()
            ->where('email', $email)
            ->where('uuid', $uuid)
            ->first();

        if (!$register) {
            return [
                'code' => 422,
                'message' => "Sorry, No Data found!",
                'data' => [
                    "uuid" => '',
                    "email" => '',
                    "phone" => '',
                ]
            ];
        }

        $register->otp_code = $otpCode;
        $register->save();
        $register->refresh();

        event(new UserRegisterOTP($register));

        return [
            'code' => 200,
            'message' => "OTP send to you Successfully!",
            'data' => [
                "uuid" => $register->uuid,
                "email" => $email,
                "phone" => $phone,
            ]
        ];

    }

    public function forgotPassword(): array
    {
        $phone = request('phone');
        $email = request('email');

        $forget = UserRegistration::query()
            ->where('email', $email)
            ->where('type', 'reset_password')
//            ->whereIn('status', ['new', 'blocked'])
            ->first();

        if ($forget && $forget->status === 'blocked') {
            return [
                'code' => 422,
                'message' => "Sorry! service unavailable, Try later!",
                'data' => null
            ];
        }

        $forget = $forget ?: new UserRegistration();
        $forget->uuid = Uuid::uuid4();
        $forget->phone = $phone;
        $forget->email = $email;
        $forget->type = 'reset_password';
        $forget->otp_code = randomOTPCode();
        $forget->otp_expired = now()->addMinutes(15);
        $forget->attempt_count = $forget ? ($forget->attempt_count + 1) : 1;
        $forget->save();

        event(new UserRegisterOTP($forget));

        return [
            'code' => 200,
            'message' => "OTP send to you Successfully!",
            'data' => [
                "uuid" => $forget->uuid,
                "email" => $email,
                "phone" => $phone,
                "reset" => true,
            ]
        ];

    }

    public function updateProfile(Request $request): array
    {
        $user = $request->user;
//        $email = $request->email;
//        $phone = $request->phone;
        $name = $request->name;
        $password = $request->password;

        $user->name = $name;
        if ($password) {
            $user->password = Hash::make($password);
        }
        $user->save();

        $data['data'] = $user;
        $data['message'] = 'Account updated successfully';
        $data['code'] = 200;
        return $data;
    }

    public function resetPassword(): array
    {
        $email = request('email');
        $token = request('token');
        $password = request('password');

        $user = Password::getUser(['email' => $email]);
        if ($user && Password::tokenExists($user, $token)) {
            $user = User::query()->where('email', $email)->first();
            $user->password = Hash::make($password);
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->password_changed_at = now();
            $user->save();

            $data['data'] = $user;
            $data['code'] = 200;
            $data['message'] = 'Password updated successfully';
            return $data;
        }

        return ['data' => null, 'code' => 422, 'message' => 'Password Updating failed'];

    }
}
