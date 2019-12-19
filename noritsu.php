#! /usr/bin/env php

<?php
/**
 * Author: Aleksey Yashenkov <alexey.yashenkov@gmail.com>
 */
require 'vendor/autoload.php';

$app = new Symfony\Component\Console\Application('PHP Noritsu log watcher', '1.0.0');
$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__.'/.env');

$app->add(new AYashenkov\commands\InfoCommand());
$app->add(new AYashenkov\commands\WatchCommand());

try {
    $app->run();
} catch (\Exception $e) {
    echo $e->getMessage();
}
