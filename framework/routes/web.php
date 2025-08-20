<?php

use App\Controllers\AboutController;
use App\Controllers\BookController;
use App\Controllers\HomeController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/about', [AboutController::class, 'index']],
    ['GET', '/books/{id:\d+}', [BookController::class, 'show']],
    ['GET', '/books/create', [BookController::class, 'create']],
    ['POST', '/books', [BookController::class, 'store']]
    , ['GET', '/dbcheck', [\App\Controllers\DbController::class, 'check']],
];
