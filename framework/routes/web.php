<?php

use App\Controllers\ActivityLogsController;
use App\Controllers\Admin\ClassroomController as AdminClassroomController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;

// New role-based controllers
use App\Controllers\Admin\UserController as AdminUserController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\Student\ClassroomController as StudentClassroomController;
use App\Controllers\Student\DashboardController as StudentDashboardController;
use App\Controllers\Student\PostController as StudentPostController;
use App\Controllers\Teacher\ClassroomController as TeacherClassroomController;
use App\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Controllers\Teacher\PostController as TeacherPostController;

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

    // ===== ADMIN ROUTES =====
    // Dashboard
    ['GET', '/admin/dashboard', [AdminDashboardController::class, 'index']],

    // User Management
    ['GET', '/admin/users', [AdminUserController::class, 'index']],
    ['GET', '/admin/users/create', [AdminUserController::class, 'create']],
    ['POST', '/admin/users', [AdminUserController::class, 'store']],
    ['GET', '/admin/users/archived', [AdminUserController::class, 'archived']],
    ['GET', '/admin/users/{id}/edit', [AdminUserController::class, 'edit']],
    ['POST', '/admin/users/{id}/update', [AdminUserController::class, 'update']],
    ['POST', '/admin/users/{id}/delete', [AdminUserController::class, 'delete']],
    ['POST', '/admin/users/{id}/restore', [AdminUserController::class, 'restore']],

    // Classroom Management
    ['GET', '/admin/classrooms', [AdminClassroomController::class, 'index']],
    ['GET', '/admin/classrooms/create', [AdminClassroomController::class, 'create']],
    ['POST', '/admin/classrooms', [AdminClassroomController::class, 'store']],
    ['GET', '/admin/classrooms/{id}', [AdminClassroomController::class, 'show']],
    ['GET', '/admin/classrooms/{id}/edit', [AdminClassroomController::class, 'edit']],
    ['POST', '/admin/classrooms/{id}/update', [AdminClassroomController::class, 'update']],
    ['POST', '/admin/classrooms/{id}/delete', [AdminClassroomController::class, 'delete']],
    ['POST', '/admin/classrooms/{id}/add-student', [AdminClassroomController::class, 'addStudent']],
    ['POST', '/admin/classrooms/{id}/remove-student/{studentId}', [AdminClassroomController::class, 'removeStudent']],

    // Activity Logs
    ['GET', '/admin/activity-logs', [ActivityLogsController::class, 'index']],
    ['GET', '/admin/my-activities', [ActivityLogsController::class, 'userActivities']],
    ['GET', '/admin/login-activities', [ActivityLogsController::class, 'loginActivities']],

    // ===== TEACHER ROUTES =====
    // Dashboard
    ['GET', '/teacher/dashboard', [TeacherDashboardController::class, 'index']],

    // Classroom Management
    ['GET', '/teacher/classrooms', [TeacherClassroomController::class, 'index']],
    ['GET', '/teacher/classrooms/create', [TeacherClassroomController::class, 'create']],
    ['POST', '/teacher/classrooms', [TeacherClassroomController::class, 'store']],
    ['GET', '/teacher/classrooms/{id}', [TeacherClassroomController::class, 'show']],
    ['GET', '/teacher/classrooms/{id}/edit', [TeacherClassroomController::class, 'edit']],
    ['POST', '/teacher/classrooms/{id}/update', [TeacherClassroomController::class, 'update']],
    ['POST', '/teacher/classrooms/{id}/add-student', [TeacherClassroomController::class, 'addStudent']],
    ['POST', '/teacher/classrooms/{id}/remove-student/{studentId}', [TeacherClassroomController::class, 'removeStudent']],

    // Teacher Post Management
    ['GET', '/teacher/classrooms/{classroomId}/posts', [TeacherPostController::class, 'index']],
    ['GET', '/teacher/classrooms/{classroomId}/posts/create', [TeacherPostController::class, 'create']],
    ['POST', '/teacher/classrooms/{classroomId}/posts', [TeacherPostController::class, 'store']],
    ['GET', '/teacher/classrooms/{classroomId}/posts/{postId}', [TeacherPostController::class, 'show']],
    ['GET', '/teacher/classrooms/{classroomId}/posts/{postId}/edit', [TeacherPostController::class, 'edit']],
    ['POST', '/teacher/classrooms/{classroomId}/posts/{postId}/update', [TeacherPostController::class, 'update']],
    ['POST', '/teacher/classrooms/{classroomId}/posts/{postId}/delete', [TeacherPostController::class, 'delete']],
    ['POST', '/teacher/classrooms/{classroomId}/posts/{postId}/comments', [TeacherPostController::class, 'addComment']],
    ['POST', '/teacher/classrooms/{classroomId}/posts/{postId}/toggle-pin', [TeacherPostController::class, 'togglePin']],

    // ===== STUDENT ROUTES =====
    // Dashboard
    ['GET', '/student/dashboard', [StudentDashboardController::class, 'index']],

    // Classroom Access
    ['GET', '/student/classes', [StudentClassroomController::class, 'index']],
    ['GET', '/student/classes/{id}', [StudentClassroomController::class, 'show']],
    ['POST', '/student/classes/join', [StudentClassroomController::class, 'join']],
    ['POST', '/student/classes/{id}/leave', [StudentClassroomController::class, 'leave']],

    // Student Post Access
    ['GET', '/student/classes/{classroomId}/posts', [StudentPostController::class, 'index']],
    ['GET', '/student/classes/{classroomId}/posts/{postId}', [StudentPostController::class, 'show']],
    ['GET', '/student/classes/{classroomId}/posts/search', [StudentPostController::class, 'search']],
    ['POST', '/student/classes/{classroomId}/posts/{postId}/comments', [StudentPostController::class, 'addComment']],
    ['POST', '/student/classes/{classroomId}/posts/{postId}/comments/{commentId}/edit', [StudentPostController::class, 'editComment']],
    ['POST', '/student/classes/{classroomId}/posts/{postId}/comments/{commentId}/delete', [StudentPostController::class, 'deleteComment']],
];
