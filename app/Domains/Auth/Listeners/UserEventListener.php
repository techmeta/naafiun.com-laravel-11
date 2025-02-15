<?php

namespace App\Domains\Auth\Listeners;

use App\Domains\Auth\Events\User\UserCreated;
use App\Domains\Auth\Events\User\UserDeleted;
use App\Domains\Auth\Events\User\UserDestroyed;
use App\Domains\Auth\Events\User\UserLoggedIn;
use App\Domains\Auth\Events\User\UserRegisterOTP;
use App\Domains\Auth\Events\User\UserRestored;
use App\Domains\Auth\Events\User\UserStatusChanged;
use App\Domains\Auth\Events\User\UserUpdated;
use App\Mail\OTPEmail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Mail;

/**
 * Class UserEventListener.
 */
class UserEventListener
{
    /**
     * @param $event
     */
    public function onLoggedIn($event): void
    {
        // Update the logging in users time & IP
        $user = $event->user;
        if ($user) {
            $user->last_login_at = now();
            $user->last_login_ip = request()->getClientIp();
            $user->save();
        }
    }

    /**
     * @param $event
     */
    public function onPasswordReset($event)
    {
        $event->user->update([
            'password_changed_at' => now(),
        ]);
    }

    /**
     * @param $event
     */
    public function onCreated($event)
    {
        activity('user')
            ->performedOn($event->user)
            ->withProperties([
                'user' => [
                    'type' => $event->user->type,
                    'name' => $event->user->name,
                    'email' => $event->user->email,
                    'active' => $event->user->active,
                    'email_verified_at' => $event->user->email_verified_at,
                ],
                'roles' => $event->user->roles->count() ? $event->user->roles->pluck('name')->implode(', ') : 'None',
                'permissions' => $event->user->permissions ? $event->user->permissions->pluck('description')->implode(', ') : 'None',
            ])
            ->log(':causer.name created user :subject.name with roles: :properties.roles and permissions: :properties.permissions');
    }

    /**
     * @param $event
     */
    public function onUpdated($event)
    {
        activity('user')
            ->performedOn($event->user)
            ->withProperties([
                'user' => [
                    'type' => $event->user->type,
                    'name' => $event->user->name,
                    'email' => $event->user->email,
                ],
                'roles' => $event->user->roles->count() ? $event->user->roles->pluck('name')->implode(', ') : 'None',
                'permissions' => $event->user->permissions ? $event->user->permissions->pluck('description')->implode(', ') : 'None',
            ])
            ->log(':causer.name updated user :subject.name with roles: :properties.roles and permissions: :properties.permissions');
    }

    /**
     * @param $event
     */
    public function onDeleted($event)
    {
        activity('user')
            ->performedOn($event->user)
            ->log(':causer.name deleted user :subject.name');
    }

    /**
     * @param $event
     */
    public function onRestored($event)
    {
        activity('user')
            ->performedOn($event->user)
            ->log(':causer.name restored user :subject.name');
    }

    /**
     * @param $event
     */
    public function onDestroyed($event)
    {
        activity('user')
            ->performedOn($event->user)
            ->log(':causer.name permanently deleted user :subject.name');
    }

    /**
     * @param $event
     */
    public function onStatusChanged($event)
    {
        activity('user')
            ->performedOn($event->user)
            ->log(':causer.name ' . ($event->status === 0 ? 'deactivated' : 'reactivated') . ' user :subject.name');
    }


    /**
     * @param $event
     */
    public function userRegisterOTP($event): void
    {
        $otpUser = $event->user;
        if ($otpUser->email) {
            Mail::to($otpUser->email)->send(new OTPEmail($otpUser));
        } else if ($otpUser->phone) {
            $text = "আপনার ওটিপি কোডটি হল: {$otpUser->otp_code} এই কোডটি কাউকে প্রদান করবেন না।";
//            (new SendSmsService($otpUser->phone, $text))->sendSingleSms();
        }
        activity('user')
            ->performedOn($event->user)
            ->log(':causer.name ' . ' user try to register');
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            UserLoggedIn::class,
            'App\Domains\Auth\Listeners\UserEventListener@onLoggedIn'
        );

        $events->listen(
            PasswordReset::class,
            'App\Domains\Auth\Listeners\UserEventListener@onPasswordReset'
        );

        $events->listen(
            UserCreated::class,
            'App\Domains\Auth\Listeners\UserEventListener@onCreated'
        );

        $events->listen(
            UserUpdated::class,
            'App\Domains\Auth\Listeners\UserEventListener@onUpdated'
        );

        $events->listen(
            UserDeleted::class,
            'App\Domains\Auth\Listeners\UserEventListener@onDeleted'
        );

        $events->listen(
            UserRestored::class,
            'App\Domains\Auth\Listeners\UserEventListener@onRestored'
        );

        $events->listen(
            UserDestroyed::class,
            'App\Domains\Auth\Listeners\UserEventListener@onDestroyed'
        );

        $events->listen(
            UserStatusChanged::class,
            'App\Domains\Auth\Listeners\UserEventListener@onStatusChanged'
        );

        $events->listen(
            UserRegisterOTP::class,
            'App\Domains\Auth\Listeners\UserEventListener@userRegisterOTP'
        );
    }
}
