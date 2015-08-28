<?php

use Symfony\Component\Finder\Finder;

require_once (__DIR__.'/vendor/autoload.php');

$phar = new Phar('phpstd.phar', 0, 'phpstd.phar');

$phar->buildFromIterator(
    Finder::create()
        ->files()
        ->in(['src', 'vendor'])
        ->exclude('tests')
        ->getIterator(),
    __DIR__
);

$phar->setStub(
    file_get_contents('src/Chromabits/Standards/Resources/phar.php')
);
