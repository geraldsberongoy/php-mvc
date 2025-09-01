<?php

use App\Controllers\ActivityLogsController;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\ClassroomController;
use App\Controllers\HomeController;
use App\Controllers\TeacherController;

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
    ['GET', '/admin/dashboard', [AdminController::class, 'showAdminDashboard']],
    ['GET', '/admin/users', [AdminController::class, 'showUsers']],
    ['GET', '/admin/users/create', [AdminController::class, 'showCreateUser']],
    ['POST', '/admin/users', [AdminController::class, 'store']],
    ['GET', '/admin/users/archived', [AdminController::class, 'showArchivedUsers']],
    ['GET', '/admin/users/{id}/edit', [AdminController::class, 'showEditUser']],
    ['POST', '/admin/users/{id}/update', [AdminController::class, 'update']],
    ['POST', '/admin/users/{id}/delete', [AdminController::class, 'delete']],
    ['POST', '/admin/users/{id}/restore', [AdminController::class, 'restore']],

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
    ['GET', '/teacher/dashboard', [TeacherController::class, 'showTeacherDashboard']],
    ['GET', '/teacher/classrooms', [TeacherController::class, 'showMyClassrooms']],
    ['GET', '/teacher/classrooms/create', [TeacherController::class, 'showCreateClassroom']],
    ['POST', '/teacher/classrooms', [TeacherController::class, 'createClassroom']],
    ['GET', '/teacher/classrooms/{id}', [TeacherController::class, 'showClassroom']],
    ['GET', '/teacher/classrooms/{id}/edit', [TeacherController::class, 'showEditClassroom']],
    ['POST', '/teacher/classrooms/{id}/update', [TeacherController::class, 'updateClassroom']],
    ['POST', '/teacher/classrooms/{id}/add-student', [TeacherController::class, 'addStudentToClassroom']],
    ['POST', '/teacher/classrooms/{id}/remove-student/{studentId}', [TeacherController::class, 'removeStudentFromClassroom']],
    ['GET', '/teacher/classes', [HomeController::class, 'teacherClasses']],

    // student routes
    ['GET', '/student/dashboard', [HomeController::class, 'showStudentDashboard']],
    ['GET', '/student/classrooms', [ClassroomController::class, 'viewClassroom']],

];
