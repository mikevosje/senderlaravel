<?php

namespace Wappz\Sender;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Wappz\Sender\Sender
 */
class SenderFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sender-laravel';
    }
}
