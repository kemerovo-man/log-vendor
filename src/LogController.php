<?php

namespace KemerovoMan\LogVendor;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

class LogController extends Controller
{

    public function index()
    {
        View::addLocation(__DIR__);
        $logs = scandir(storage_path('logs'));
        $logs = array_filter($logs, function ($log) {
            return strpos($log, '.json') !== false;
        });
        natsort($logs);
        $res = [];
        foreach ($logs as $log) {
            $date = substr($log, 0, 10);
            if (!isset($res[$date])) {
                $res[$date] = [];
            }
            $res[$date][] = $log;
        }
        return View::make('log')->with('dateLogs', $res);
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
