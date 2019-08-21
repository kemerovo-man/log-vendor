# Лог вендор для Laravel 5.7, 5.8

Добавляет в проект возможность логировать сразу в текстовый файл и в json, и читать json логи в браузере.
Настроики в config/log.php
Добавляет в проект роуты:
/logs - список логов,
/logs/{file} - посмотреть json лог

## Установка

1. выполнить
```
require kemerovo-man/log-vendor
```
для Laravel 5.7
```
    "require": {
        "kemerovo-man/log-vendor": "5.7.*"
    }
```

для Laravel 5.8
```
    "require": {
        "kemerovo-man/log-vendor": "5.8.*"
    }
```

2. добавить в app.conf
```
    'providers' => [
        KemerovoMan\LogVendor\LogVendorServiceProvider::class
    ]
```
3. php artisan vendor:publish

4. настроить config/log.php

можно закрыть роуты /logs, /logs/{file} мидлварами

Например:
```
'middleware' => ['web', 'auth']
```
5. Написать конфиг для всех логов и добавить методы на фасаде

7. Можно изменить Exceptions/Handler.php

```
    public function report(Exception $exception)
    {
        if ($this->shouldntReport($exception)) {
            return;
        }
        parent::report($exception);
        \Log::laravelReport($exception);
    }
```