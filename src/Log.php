<?php

namespace KemerovoMan\LogVendor;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;
use \Carbon\Carbon;

class Log
{

    protected static function log($logName, $params)
    {
        static::deleteOldFiles();
        $info = '';
        foreach ($params as $key => $value) {
            $info .= static::wrap($key, $value);
        }
        static::txtLog($logName, $info);
        static::jsonLog($logName, $params);
    }

    protected static function wrap($key, $value)
    {
        $stringValue = $value;
        if (is_array($value)) {
            $stringValue = json_encode($value);
        }
        return $key . ': [' . $stringValue . '] ';
    }

    protected static function deleteOldFiles()
    {
        $files = glob(storage_path('logs') . '/*');
        $now = time();
        foreach ($files as $file) {
            if (is_file($file)) {
                $days = config('log.storeDays', 30);
                if ($now - filemtime($file) >= 60 * 60 * 24 * $days) {
                    if (file_exists($file)) {
                        try {
                            unlink($file);
                        } catch (\Exception $e) {
                            
                        }
                    }
                }
            }
        }
    }

    protected static function txtLog($name, $info)
    {
        $log = new Logger($name);
        $date = Carbon::now();
        $path = storage_path('logs') . '/' . $date->toDateString() . '_' . $name . '.log';
        $streamHandler = new StreamHandler($path);
        $format = new LineFormatter("[%datetime%] %message%\n", "d/M/Y:H:i:s O");
        $streamHandler->setFormatter($format);
        $log->pushHandler($streamHandler);
        $log->addInfo($info);
    }

    protected static function jsonLog($name, $params)
    {
        if (!$params) {
            return;
        }
        $sizeLimit = 1024 * 1024; // 1 Mb
        $counter = 0;
        $date = Carbon::now();
        do {
            $counter++;
            $jsonLog = storage_path('logs') . '/' . $date->toDateString() . '_' . $name . '_' . $counter . '.json';
        } while (file_exists($jsonLog) && filesize($jsonLog) > $sizeLimit);
        $name = $name . '_' . $counter;
        $log = new Logger($name);
        $streamHandler = new StreamHandler($jsonLog);
        $format = new LineFormatter("%message%,\n");
        $streamHandler->setFormatter($format);
        $log->pushHandler($streamHandler);
        $logInfo = [];
        $logInfo['dateTimeLog'] = $date->toDateTimeString();
        $logInfo = array_merge($logInfo, $params);
        $logInfo = json_encode($logInfo);
        if ($logInfo) {
            $log->addInfo($logInfo);
        }
    }

    public static function info($info)
    {
        \Illuminate\Support\Facades\Log::info($info);
    }

    public static function laravelReport(\Exception $e)
    {
        $params = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ];
        static::jsonLog('laravel', $params);
    }

    public static function testLog($message, $param = 'test')
    {
        static::log('test', [
            'message' => $message,
            'param' => $param
        ]);
    }
}