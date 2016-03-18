#!/usr/bin/env php

<?php

if (defined('HHVM_VERSION_ID')) {
    if (HHVM_VERSION_ID < 30600) {
        fwrite(STDERR, "HHVM needs to be a minimum version of HHVM 3.6.0\n");
        exit(1);
    }
} elseif (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50600) {
    fwrite(STDERR, "PHP needs to be a minimum version of PHP 5.6.0\n");
    exit(1);
}

set_error_handler(function ($severity, $message, $file, $line) {
    if ($severity & error_reporting()) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

Phar::mapPhar('phpstd.phar');

require_once 'phar://phpstd.phar/vendor/autoload.php';

use SellerLabs\Standards\Console\Application;

$app = new Application();
$app->run();

__HALT_COMPILER();
