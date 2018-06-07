<?php

class Log extends \KemerovoMan\LogVendor\Log
{
    // modify me
    public static function test($message)
    {
        static::log('test_file', [
            'message' => $message
        ]);
    }

}