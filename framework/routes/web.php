<?php

use App\Controllers\ActivityLogsController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\ClassroomController;

return [
    ['GET', '/', [HomeController::class, 'index']],

    // auth routes
    ['GET', '/login', [AuthController::class, 'showLogin']],
    ['POST', '/login', [AuthController::class, 'login']],
    ['POST', '/logout', [AuthController::class, 'logout']],
    ['GET', '/register', [AuthController::class, 'showRegister']],
    ['POST', '/register', [AuthController::class, 'register']],

    // dashboard route - role-based redirect
    ['GET', '/dashboard', [HomeController::class, 'redirectDashboard']],

    // admin routes (only accessible by admin)
    ['GET', '/admin/dashboard', [HomeController::class, 'showAdminDashboard']],
    ['GET', '/admin/users', [UserController::class, 'showUsers']],
    ['GET', '/admin/users/create', [UserController::class, 'showCreateUser']],
    ['POST', '/admin/users', [UserController::class, 'store']],
    ['GET', '/admin/users/archived', [UserController::class, 'showArchivedUsers']],
    ['GET', '/admin/users/{id}/edit', [UserController::class, 'showEditUser']],
    ['POST', '/admin/users/{id}/update', [UserController::class, 'update']],
    ['POST', '/admin/users/{id}/delete', [UserController::class, 'delete']],
    ['POST', '/admin/users/{id}/restore', [UserController::class, 'restore']],

    // classroom management routes (admin only)
    ['GET', '/admin/classrooms', [ClassroomController::class, 'index']],
    ['GET', '/admin/classrooms/create', [ClassroomController::class, 'create']],
    ['POST', '/admin/classrooms', [ClassroomController::class, 'store']],
    ['GET', '/admin/classrooms/{id}', [ClassroomController::class, 'show']],
    ['GET', '/admin/classrooms/{id}/edit', [ClassroomController::class, 'edit']],
    ['POST', '/admin/classrooms/{id}/update', [ClassroomController::class, 'update']],
    ['POST', '/admin/classrooms/{id}/delete', [ClassroomController::class, 'delete']],
    ['POST', '/admin/classrooms/{id}/add-student', [ClassroomController::class, 'addStudent']],
    ['POST', '/admin/classrooms/{id}/remove-student/{studentId}', [ClassroomController::class, 'removeStudent']],

    // activity logs routes
    ['GET', '/admin/activity-logs', [ActivityLogsController::class, 'index']],
    ['GET', '/admin/my-activities', [ActivityLogsController::class, 'userActivities']],
    ['GET', '/admin/login-activities', [ActivityLogsController::class, 'loginActivities']],

    // teacher routes
    ['GET', '/teacher/dashboard', [HomeController::class, 'showTeacherDashboard']],
    ['GET', '/teacher/classes', [HomeController::class, 'teacherClasses']],

    // student routes
    ['GET', '/student/dashboard', [HomeController::class, 'showStudentDashboard']],
    ['GET', '/student/classrooms', [ClassroomController::class, 'viewClassroom']],

];
