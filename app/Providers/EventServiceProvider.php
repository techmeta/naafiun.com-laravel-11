<?php

namespace App\Providers;

use App\Domains\Auth\Listeners\RoleEventListener;
use App\Domains\Auth\Listeners\UserEventListener;
use App\Domains\Order\Listeners\OrderEventListener;
//use App\Listeners\GeneralEventListener;
//use App\Listeners\Tracking\TrackingListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Class event subscribers.
     *
     * @var array
     */
    protected $subscribe = [
//        GeneralEventListener::class,
        RoleEventListener::class,
        UserEventListener::class,
        OrderEventListener::class,
//        TrackingListener::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
