<?php

namespace App\Domains\Auth\Events\User;

use App\Models\UserRegistration;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserRegisterOTP.
 */
class UserRegisterOTP
{
    use SerializesModels;

    /**
     * @var UserRegistration
     */
    public UserRegistration $user;

    /**
     * @param UserRegistration $user
     */
    public function __construct(UserRegistration $user)
    {
        $this->user = $user;
    }
}
