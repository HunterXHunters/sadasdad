<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
		'App\Events\test'=>[
			'App\Listeners\testl',
		],
		'App\Events\scoapi_BindEseals'=>[
			'App\Listeners\list_scoapi_bindEseal',
		],
				'App\Events\scoapi_MapEseals'=>[
			'App\Listeners\list_scoapi_MapEseals',
		]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
			Event::listen('eventK', function ($foo, $bar) {
				echo "in eventK"; exit;	
			//
			});
        //
    }
}
