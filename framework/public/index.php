<?php

use Gerald\Framework\Http\Kernel;
use Gerald\Framework\Http\Request;
use Gerald\Framework\Http\Response;


define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/vendor/autoload.php';

$request = Request::create();

$kernel = new Kernel();
$response = $kernel->handle($request);
$response->send();
