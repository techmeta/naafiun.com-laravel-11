<?php

namespace App\Domains\Auth\Events\User;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserRegisterOTP.
 */
class UserRegisterOTP
{
    use SerializesModels;

    /**
     * @var User
     */
    public User $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
