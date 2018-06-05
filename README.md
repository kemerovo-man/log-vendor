# Лог вендор для Laravel 5.6

Добавляет в проект возможность логировать сразу в текстовый файл и в json, и читать json логи в браузере.
Настроики в config/log.php
Добавляет в проект роуты:
/logs - список логов,
/logs/{file} - посмотреть json лог

## Установка

1. добавить в composer.json
```
    "require": {
        "KemerovoMan/LogVendor": "dev-master"
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

5. можно расширить класс

Например:
```
<?php

class Log extends \KemerovoMan\LogVendor\Log
{
    public static function oracleLog($id, $result)
    {
        static::logToFiles('oracle', [
            'id' => $id,
            'result' => $result
        ]);
    }
}
```
