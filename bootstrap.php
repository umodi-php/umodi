#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload_runtime.php';

spl_autoload_register(static function ($class) {
    $filename = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($filename)) {
        include $filename;
    }
});

include __DIR__ . '/src/functions.php';
