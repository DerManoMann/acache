<?php

/*
* This file is part of the ACache library.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Radebatz\ACache\Tests', __DIR__);

// add custom ini setting if file exists
if (file_exists(dirname(__DIR__) . '/phpunit.ini')) {
    $settings = parse_ini_file(dirname(__DIR__) . '/phpunit.ini', true);
    foreach ($settings as $group => $kvp) {
        foreach ($kvp as $key => $value) {
            $dKey = strtoupper(sprintf('phpunit_%s_%s', $group, $key));
            echo 'Default override: ' . $dKey . ' = ' . $value . "\n";
            define($dKey, $value);
        }
    }
}

if (!class_exists('PHPUnit_Framework_TestCase')) {
    class PHPUnit_Framework_TestCase extends \PHPUnit\Framework\TestCase
    {
    }
}
