# Лог вендор для Laravel 5.6

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
или добавить в composer.json
```
    "require": {
        "kemerovo-man/log-vendor": "0.0.*"
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

5. Создать папку app/Log, в ней создать файл Log.php

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

6. Вызвать в коде можно так: Log::oracleLog(1, 'ok');