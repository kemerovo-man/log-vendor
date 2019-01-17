<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Log
 * @method static void testLog(string $message, int $parameter1)
 */
class Log extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'log.service';
    }
}
