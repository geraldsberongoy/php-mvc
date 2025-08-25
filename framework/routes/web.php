<?php

use App\Controllers\ActivityLogsController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\UserController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/users/create', [UserController::class, 'create']],
    ['POST', '/users', [UserController::class, 'store']],

    // auth routes
    ['GET', '/login', [AuthController::class, 'showLogin']],
    ['POST', '/login', [AuthController::class, 'login']],
    ['POST', '/logout', [AuthController::class, 'logout']],
    ['GET', '/dashboard', [HomeController::class, 'showDashboard']],
    ['GET', '/register', [AuthController::class, 'showRegister']],
    ['POST', '/register', [AuthController::class, 'register']],

    // activity logs routes
    ['GET', '/activity-logs', [ActivityLogsController::class, 'index']],
    ['GET', '/my-activities', [ActivityLogsController::class, 'userActivities']],
    ['GET', '/login-activities', [ActivityLogsController::class, 'loginActivities']],
];
