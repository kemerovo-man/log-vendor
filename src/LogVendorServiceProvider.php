<?php

namespace KemerovoMan\LogVendor;

use Illuminate\Support\ServiceProvider;

class LogVendorServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        require_once __DIR__ . '/' . 'routes.php';
        app()->bind(Log::class, function () {
            return new Log();
        });
        $this->publishes([
            __DIR__ . '/Config/log.php' => config_path('log.php'),
        ]);
    }

}
