<?php

use App\Controllers\AboutController;
use App\Controllers\HomeController;
use App\Controllers\BookController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/about', [AboutController::class, 'index']],
    ['GET', '/books/{id:\d+}', [BookController::class, 'show']],
    ['GET', '/books/create', [BookController::class, 'create']],
    ['POST', '/books', [BookController::class, 'store']]
];