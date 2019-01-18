<?php

namespace KemerovoMan\LogVendor;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;
use \Carbon\Carbon;

use \Illuminate\Support\Facades\Log as LaravelLog;

class Log
{

    protected function log($logName, $params)
    {
        $disable = config('log.disable', false);
        if ($disable)
            return;
        $this->deleteOldFiles();
        $info = '';
        foreach ($params as $key => $value) {
            $info .= static::wrap($key, $value);
        }
        $this->txtLog($logName, $info);
        $this->jsonLog($logName, $params);
    }

    protected function wrap($key, $value)
    {
        $stringValue = $value;
        if (is_array($value)) {
            $stringValue = json_encode($value);
        }
        return $key . ': [' . $stringValue . '] ';
    }

    protected function deleteOldFiles()
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

    protected function txtLog($name, $info)
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

    protected function jsonLog($name, $params)
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

    public function laravelReport(\Exception $e)
    {
        $params = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ];
        $this->jsonLog('laravel', $params);
    }

    protected function getPhpDocs($class)
    {
        $reflectionClass = new \ReflectionClass($class);
        $phpDocs = $reflectionClass->getDocComment();
        $phpDocs = explode("\n", $phpDocs);
        $phpDocs = array_values(array_filter($phpDocs, function ($phpDoc) {
            return strpos($phpDoc, '@method static');
        }));
        return $phpDocs;
    }

    protected function getPhpDocArgs($phpDocs, $name)
    {
        foreach ($phpDocs as $phpDoc) {
            preg_match('/\* @method static void (.*)\((.*)\)/', $phpDoc, $matches);
            if (count($matches) == 3 && $matches[1] == $name) {
                return array_map(function ($arg) {
                    return trim($arg);
                }, explode(',', $matches[2]));
            }
        }
        return null;
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

        $facades = config('log.facades');
        if (!$facades) {
            return;
        }
        foreach ($facades as $facade) {
            $phpDocs = $this->getPhpDocs($facade);
            $args = $this->getPhpDocArgs($phpDocs, $name);
            $data = [];
            foreach ($args as $i => $arg) {
                preg_match('/.?\$(.*)/', $arg, $matches);
                if (count($matches) == 2) {
                    $data[$matches[1]] = $arguments[$i];
                }
            }
            if ($data) {
                $this->log(snake_case($name), $data);
            }
        }
    }
}