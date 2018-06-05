<?php

namespace KemerovoMan\LogVendor;

use Illuminate\Support\ServiceProvider;

class LogVendorServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    protected function req($dir)
    {
        $scan = glob($dir . DIRECTORY_SEPARATOR . "*");
        foreach ($scan as $path) {
            if (preg_match('/\.php$/', $path)) {
                require_once $path;
            } elseif (is_dir($path)) {
                $this->req($path);
            }
        }
    }

    public function register()
    {
        require_once __DIR__ . '/' . 'routes.php';
        $this->publishes([
            __DIR__ . '/Config/log.php' => config_path('log.php'),
        ]);
        $path = app_path() . '/Log/';
        if (is_dir($path)) {
            $this->req($path);
        }
    }

}
