<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
$dotenv->required(
    [
        'GARAPON_TV_ADDRESS',
        'GARAPON_TV_PORT',
        'GARAPON_LOGINID',
        'GARAPON_MD5PSWD',
        'GARAPON_DEV_ID'
    ]
);

Makotokw\Garapon\TestCase::$dotenv = $dotenv;
