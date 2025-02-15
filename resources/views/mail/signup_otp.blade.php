@component('mail::message')
# One-Time Password (OTP) for Account Verification

Thank you for choosing {{ config('app.name') }} for your account needs. To ensure the security of your account, we have initiated the process of verifying your account.

Your One-Time Password (OTP) is: **{{ $otpUser->otp_code }}**

Please use this OTP to complete the verification process. If you did not initiate this request or have any concerns regarding your account security, please contact our customer support immediately.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
