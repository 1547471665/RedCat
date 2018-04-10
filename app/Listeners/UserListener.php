<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use App\Events\UserEvent;

class UserListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

        //
    }

    /**
     * Handle the event.
     *
     * @param  ExampleEvent $event
     * @return void
     */
    public function handle(UserEvent $event)
    {
        //
    }
}
