<?php

namespace KemerovoMan\LogVendor;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;
use \Carbon\Carbon;

use \Illuminate\Support\Facades\Log as LaravelLog;

class Log
{

    protected static function log($logName, $params)
    {
        $disable = config('log.disable', false);
        if ($disable)
            return;
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
        $lineFormat = config('log.format', "[%datetime%] %message%\n");
        $dateFormat = config('log.dateFormat', "d/M/Y:H:i:s O");
        $format = new LineFormatter($lineFormat, $dateFormat);
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

    public function __call($name, $arguments)
    {
        $methods = [
            'info',
            'alert',
            'debug',
            'critical',
            'emergency',
            'error',
            'notice',
            'warning',
        ];

        if (in_array($name, $methods)) {
            call_user_func_array([LaravelLog::class, $name], $arguments);
            return;
        }

        $data = [];
        $config = config('log.logs.' . $name);
        if ($config && !empty($config['keys'])
            && count($arguments) == count($config['keys'])) {
            foreach ($config['keys'] as $i => $key) {
                $data[$key] = $arguments[$i];
            }
            $this->log(snake_case($name), $data);
        }
    }
}