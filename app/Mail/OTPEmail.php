<?php

namespace App\Mail;

use App\Models\UserRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OTPEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var UserRegistration
     */
    public UserRegistration $otpUser;

    /**
     * @param UserRegistration $otpUser
     */
    public function __construct(UserRegistration $otpUser)
    {
        $this->otpUser = $otpUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): OTPEmail
    {
        $appName = config('app.name');
        return $this->markdown('mail.signup_otp')
            ->subject("$appName - One-Time Password (OTP) for Account Verification")
            ->from(config('mail.form.address'));
    }
}
