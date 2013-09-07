<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
if (is_object($loader)) {
    $loader->add('Makotokw\\Garapon\\', __DIR__);
}
