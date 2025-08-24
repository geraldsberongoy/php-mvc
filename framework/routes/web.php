<?php

use App\Controllers\AboutController;
use App\Controllers\AuthController;
use App\Controllers\BookController;
use App\Controllers\HomeController;
use App\Controllers\UserController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/about', [AboutController::class, 'index']],
    ['GET', '/books/{id:\d+}', [BookController::class, 'show']],
    ['GET', '/books/create', [BookController::class, 'create']],
    ['GET', '/users/create', [UserController::class, 'create']],
    ['POST', '/users', [UserController::class, 'store']],

    // auth routes
    ['GET', '/login', [AuthController::class, 'showLogin']],
    ['POST', '/login', [AuthController::class, 'login']],
    ['POST', '/logout', [AuthController::class, 'logout']],
    ['GET', '/dashboard', [HomeController::class, 'showDashboard']],
];
