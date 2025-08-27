<?php

use Gerald\Framework\Http\Kernel;
use Gerald\Framework\Http\Request;

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set timezone to GMT+8 (Philippine Standard Time)
date_default_timezone_set('Asia/Manila');

$request = Request::create();

$kernel   = new Kernel();
$response = $kernel->handle($request);
$response->send();
