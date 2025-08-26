<?php

use App\Controllers\ActivityLogsController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\UserController;

return [
    ['GET', '/', [HomeController::class, 'index']],

    // auth routes
    ['GET', '/login', [AuthController::class, 'showLogin']],
    ['POST', '/login', [AuthController::class, 'login']],
    ['POST', '/logout', [AuthController::class, 'logout']],
    ['GET', '/register', [AuthController::class, 'showRegister']],
    ['POST', '/register', [AuthController::class, 'register']],

    // dashboard route - role-based redirect
    ['GET', '/dashboard', [HomeController::class, 'showDashboard']],

    // admin routes (only accessible by admin)
    ['GET', '/admin/dashboard', [HomeController::class, 'adminDashboard']],
    ['GET', '/admin/users', [UserController::class, 'index']],
    ['GET', '/admin/users/create', [UserController::class, 'create']],
    ['POST', '/admin/users', [UserController::class, 'store']],
    ['GET', '/admin/users/{id}/edit', [UserController::class, 'edit']],
    ['POST', '/admin/users/{id}/update', [UserController::class, 'update']],
    ['POST', '/admin/users/{id}/delete', [UserController::class, 'delete']],

    // activity logs routes
    ['GET', '/admin/activity-logs', [ActivityLogsController::class, 'index']],
    ['GET', '/admin/my-activities', [ActivityLogsController::class, 'userActivities']],
    ['GET', '/admin/login-activities', [ActivityLogsController::class, 'loginActivities']],

    // teacher routes
    ['GET', '/teacher/dashboard', [HomeController::class, 'teacherDashboard']],

    // student routes
    ['GET', '/student/dashboard', [HomeController::class, 'studentDashboard']],


];
