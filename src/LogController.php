<?php

namespace KemerovoMan\LogVendor;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class LogController extends Controller
{

    public function index()
    {
        $search = Input::get('search');
        $title = config('log.title');
        View::addLocation(__DIR__);
        $logs = $this->getJsonLogs();
        if ($search) {
            $logs = array_filter($logs, function ($log) use ($search) {
                $content = file_get_contents(storage_path('logs') . '/' . $log);
                return strpos($content, $search) !== false;
            });
        }
        $res = $this->prepareLogs($logs);
        return View::make('log')
            ->with('title', $title)
            ->with('dateLogs', $res)
            ->with('search', $search);
    }

    private function getJsonLogs()
    {
        $logs = scandir(storage_path('logs'));
        return array_filter($logs, function ($log) {
            return strpos($log, '.json') !== false;
        });
    }

    private function prepareLogs($logs)
    {
        natsort($logs);
        $res = [];
        foreach ($logs as $log) {
            $words = explode('_', $log);
            $date = $words[0];
            $firstWord = $words[1];
            if (!isset($res[$date][$firstWord])) {
                $res[$date][$firstWord] = [];
            }
            if (!isset($res[$date]['ALL'])) {
                $res[$date]['ALL'] = [];
            }
            $res[$date][$firstWord][] = $log;
            $res[$date]['ALL'][] = $log;
        }
        return $res;
    }

    public function show($file)
    {
        $path = storage_path('logs/' . $file);
        if (file_exists($path)) {
            $file = file_get_contents($path);
            if (!json_decode($file)) {
                $file = substr($file, 0, -2);
                $file = '[' . $file . ']';
            }
            $file = json_decode($file);
            return response()->json($file);
        } else {
            abort(404);
        }
    }
}
