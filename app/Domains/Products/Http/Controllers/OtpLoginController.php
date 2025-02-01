<?php

namespace App\Domains\Products\Http\Controllers;

use App\Domains\Auth\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Class OtpLoginController.
 */
class OtpLoginController extends Controller
{
    use AuthenticatesUsers;


    public function loginWithOtp()
    {
        $phone = request('phone');
        $phone = str_replace('+88', '', $phone);
        $user = User::where('phone', $phone)->first();
        $otp = mt_rand(100000, 999999);

        $status = false;

        if ($user) {
            if (!$user->email) {
                $user->email = $phone . '@otpLogin.com';
            }
            $user->otp_code = $otp;
            $user->save();
            if ($user->otp_code) {
                $status = true;
            }
        } else {
            $user = new User();
            $user->name = 'OTP User';
            $user->timezone = 'Asia/Dhaka';
            $user->email_verified_at = now();
            $user->active = 1;
            $user->phone = $phone;
            $user->email = $phone . '@otpLogin.com';
            $user->password = Hash::make('naafiun@' . $phone);
            $user->otp_code = $otp;
            $user->save();
            if ($user->otp_code) {
                $status = true;
            }
        }

        $appUrl = 'https://naafiun.com';
        $txt = "{$otp} is your One Time Password (OTP) for Naafiun 2FA, validity is 10 minutes. Helpline 01407700600 {$appUrl}";

        if ($user && $phone) {
            try {
                $response = singleSms($txt, $phone);
            } catch (\Exception $ex) {
                Log::error('Login OTP sending Failed::' . $ex->getMessage());
            }
        }

        $user_id = $user->id ?? null;

        return response()->json(['status' => $status, 'phone' => $phone, 'user_id' => $user_id]);
    }


    public function OtpCodeVerify()
    {
        $otp_code = request('otp_code');
        $userPhone = request('userPhone');
        $userId = request('userId');
        $status = false;

        $user = User::where('phone', $userPhone)
            ->where('otp_code', $otp_code)
            ->whereNotNull('active')
            ->where('id', $userId)
            ->first();

        if ($user) {
            $status = true;
            if (!$user->otp_verified_at) {
                $user->otp_verified_at = now();
            }
            $user->otp_code = null;
            $user->save();
            Auth::login($user, true);
            return response(['status' => $status, 'user' => $user]);
        }

        return response(['status' => $status]);
    }
}
