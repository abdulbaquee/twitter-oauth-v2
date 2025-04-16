<?php
/**
 * PHPUnit test configuration file
 */

$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($composerAutoload)) {
    die(
        "\nYou need to set up the project dependencies using Composer:\n\n" .
        "    composer install\n\n" .
        "See https://getcomposer.org/download/ for instructions on installing Composer\n"
    );
}
require_once $composerAutoload;
