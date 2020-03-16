<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Log
 * @method static void testLog(string $message, int $parameter1)
 *
 * @method static void emergency(string $message, array $context = [])
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void log($level, string $message, array $context = [])
 *
 * @method static void laravelReport(\Exception $e)
 */
class LogExample extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \KemerovoMan\LogVendor\Log::class;
    }
}
