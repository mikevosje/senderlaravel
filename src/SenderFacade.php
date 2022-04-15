<?php

namespace App\Packages\Sender;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Packages\Sender\Sender
 */
class SenderFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sender-laravel';
    }
}
