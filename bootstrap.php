#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload_runtime.php';

spl_autoload_register(static function ($class) {
    $filename = __DIR__ . '/src/' . substr(str_replace('\\', '/', $class), 5) . '.php';
    if (file_exists($filename)) {
        include $filename;
    }
});

include_once __DIR__ . '/src/functions.php';

$directory = __DIR__ . '/src/Assert';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$it->rewind();
while($it->valid()) {
    if (!$it->isDot()) {
        include_once $it->key();
    }

    $it->next();
}
