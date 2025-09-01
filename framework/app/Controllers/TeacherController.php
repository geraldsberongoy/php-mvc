<?php
namespace App\Controllers;

use App\Models\ActivityLogs;
use App\Models\Classroom;
use App\Models\User;
use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Response;
use Gerald\Framework\Http\Session;

class TeacherController extends AbstractController
{
    public function showTeacherDashboard(): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        // Check if user is teacher
        if (($userData['role'] ?? 'student') !== 'teacher') {
            return Response::redirect('/dashboard');
        }

        // Get real dashboard data for teacher
        $dashboardData = $this->getTeacherDashboardData($userId);

        return $this->render('teacher/dashboard.html.twig', [
            'user_id'        => $userId,
            'first_name'     => $session->get('first_name') ?? 'Teacher',
            'user_role'      => 'teacher',
            'dashboard_data' => $dashboardData,
            'session'        => $session->all(),
            'current_route'  => '/teacher/dashboard',
        ]);
    }

    public function showMyClassrooms(): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        // Check if user is teacher
        if (($userData['role'] ?? 'student') !== 'teacher') {
            return Response::redirect('/dashboard');
        }

        $classroomModel = new Classroom();
        $classrooms     = $this->getTeacherClassroomsWithStats($userId);

        // Get messages from URL parameters
        $successMessage = $this->request->getQuery('success');
        $errorMessage   = $this->request->getQuery('error');

        return $this->render('teacher/classrooms/index.html.twig', [
            'classrooms'      => $classrooms,
            'user_role'       => $session->get('user_role'),
            'first_name'      => $session->get('first_name'),
            'success_message' => $successMessage,
            'error_message'   => $errorMessage,
            'current_route'   => '/teacher/classrooms',
            'session'         => $session->all(),
        ]);
    }

    public function showCreateClassroom(): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        // Check if user is teacher
        if (($userData['role'] ?? 'student') !== 'teacher') {
            return Response::redirect('/dashboard');
        }

        return $this->render('teacher/classrooms/create.html.twig', [
            'user_role'     => $session->get('user_role'),
            'first_name'    => $session->get('first_name'),
            'current_route' => '/teacher/classrooms',
            'session'       => $session->all(),
        ]);
    }

    public function showClassroom(string $id): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        // Check if user is teacher
        if (($userData['role'] ?? 'student') !== 'teacher') {
            return Response::redirect('/dashboard');
        }

        $classroomModel = new Classroom();
        $classroom      = $classroomModel->getWithTeacherDetails((int) $id);

        if (! $classroom) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $students          = $classroomModel->getStudentsWithProfiles((int) $id);
        $availableStudents = $classroomModel->getAvailableStudents((int) $id);

                           // Get assignments for this classroom (when Assignment model is ready)
        $assignments = []; // Placeholder

        // Get messages from URL parameters
        $successMessage = $this->request->getQuery('success');
        $errorMessage   = $this->request->getQuery('error');

        return $this->render('teacher/classrooms/show.html.twig', [
            'classroom'          => $classroom,
            'students'           => $students,
            'available_students' => $availableStudents,
            'assignments'        => $assignments,
            'user_role'          => $session->get('user_role'),
            'first_name'         => $session->get('first_name'),
            'success_message'    => $successMessage,
            'error_message'      => $errorMessage,
            'current_route'      => '/teacher/classrooms',
            'session'            => $session->all(),
        ]);
    }

    public function showEditClassroom(string $id): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        // Check if user is teacher
        if (($userData['role'] ?? 'student') !== 'teacher') {
            return Response::redirect('/dashboard');
        }

        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $id);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        // Get error message from URL parameters
        $errorMessage = $this->request->getQuery('error');

        return $this->render('teacher/classrooms/edit.html.twig', [
            'classroom'     => $classroom,
            'user_role'     => $session->get('user_role'),
            'first_name'    => $session->get('first_name'),
            'error_message' => $errorMessage,
            'current_route' => '/teacher/classrooms',
            'session'       => $session->all(),
        ]);
    }

    // ACTIONS

    public function createClassroom(): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        // Check if user is teacher
        if (($userData['role'] ?? 'student') !== 'teacher') {
            return Response::redirect('/dashboard');
        }

        $name        = $this->request->getPost('name') ?? null;
        $code        = $this->request->getPost('code') ?? null;
        $description = $this->request->getPost('description') ?? null;

        if (! $name || ! $code) {
            return $this->render('teacher/classrooms/create.html.twig', [
                'error'      => 'Name and code are required',
                'user_role'  => $session->get('user_role'),
                'first_name' => $session->get('first_name'),
                'session'    => $session->all(),
            ]);
        }

        try {
            $classroomModel = new Classroom();

            // Check if code already exists
            $existingClassroom = $classroomModel->findByCode($code);
            if ($existingClassroom) {
                return $this->render('teacher/classrooms/create.html.twig', [
                    'error'      => 'Classroom code already exists',
                    'user_role'  => $session->get('user_role'),
                    'first_name' => $session->get('first_name'),
                    'session'    => $session->all(),
                ]);
            }

            // Create classroom
            $classroomId = $classroomModel->create([
                'teacher_id'  => $userId,
                'name'        => $name,
                'code'        => $code,
                'description' => $description,
            ]);

            // Log activity
            $activityModel = new ActivityLogs();
            $activityModel->log($userId, 'created_classroom',
                "Created classroom '{$name}' with code '{$code}'");

            return Response::redirect('/teacher/classrooms?' . http_build_query(['success' => 'Classroom created successfully']));
        } catch (\Exception $e) {
            return $this->render('teacher/classrooms/create.html.twig', [
                'error'      => 'Error creating classroom: ' . $e->getMessage(),
                'user_role'  => $session->get('user_role'),
                'first_name' => $session->get('first_name'),
                'session'    => $session->all(),
            ]);
        }
    }

    public function updateClassroom(string $id): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        // Check if user is teacher
        if (($userData['role'] ?? 'student') !== 'teacher') {
            return Response::redirect('/dashboard');
        }

        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $id);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $name        = $this->request->getPost('name') ?? $classroom['name'];
        $code        = $this->request->getPost('code') ?? $classroom['code'];
        $description = $this->request->getPost('description') ?? $classroom['description'];

        try {
            // Check if code already exists (excluding current classroom)
            if ($code !== $classroom['code']) {
                $existingClassroom = $classroomModel->findByCode($code);
                if ($existingClassroom) {
                    return Response::redirect('/teacher/classrooms/' . $id . '/edit?error=Classroom code already exists');
                }
            }

            // Update classroom
            $classroomModel->updateClassroom((int) $id, [
                'name'        => $name,
                'code'        => $code,
                'description' => $description,
            ]);

            // Log activity
            $activityModel = new ActivityLogs();
            $activityModel->log($userId, 'updated_classroom',
                "Updated classroom '{$name}' (ID: {$id})");

            return Response::redirect('/teacher/classrooms?' . http_build_query(['success' => 'Classroom updated successfully']));
        } catch (\Exception $e) {
            return Response::redirect('/teacher/classrooms/' . $id . '/edit?error=Error updating classroom: ' . $e->getMessage());
        }
    }

    public function addStudentToClassroom(string $id): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        // Check if user is teacher
        if (($userData['role'] ?? 'student') !== 'teacher') {
            return Response::redirect('/dashboard');
        }

        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $id);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $studentId = $this->request->getPost('student_id');
        if (! $studentId) {
            return Response::redirect('/teacher/classrooms/' . $id . '?error=Student ID is required');
        }

        try {
            // Add student to classroom
            $success = $classroomModel->addStudent((int) $id, (int) $studentId);

            if ($success) {
                // Get student name for logging
                $studentData = $userModel->findWithDetails((int) $studentId);
                $studentName = ($studentData['first_name'] ?? '') . ' ' . ($studentData['last_name'] ?? '');

                // Log activity
                $activityModel = new ActivityLogs();
                $activityModel->log($userId, 'added_student_to_classroom',
                    "Added student '{$studentName}' to classroom '{$classroom['name']}'");

                return Response::redirect('/teacher/classrooms/' . $id . '?' . http_build_query(['success' => 'Student added successfully']));
            } else {
                return Response::redirect('/teacher/classrooms/' . $id . '?error=Failed to add student');
            }
        } catch (\Exception $e) {
            return Response::redirect('/teacher/classrooms/' . $id . '?error=Error adding student: ' . $e->getMessage());
        }
    }

    public function removeStudentFromClassroom(string $id, string $studentId): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        // Check if user is teacher
        if (($userData['role'] ?? 'student') !== 'teacher') {
            return Response::redirect('/dashboard');
        }

        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $id);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        try {
            // Get student name for logging before removal
            $studentData = $userModel->findWithDetails((int) $studentId);
            $studentName = ($studentData['first_name'] ?? '') . ' ' . ($studentData['last_name'] ?? '');

            // Remove student from classroom
            $success = $classroomModel->removeStudent((int) $id, (int) $studentId);

            if ($success) {
                // Log activity
                $activityModel = new ActivityLogs();
                $activityModel->log($userId, 'removed_student_from_classroom',
                    "Removed student '{$studentName}' from classroom '{$classroom['name']}'");

                return Response::redirect('/teacher/classrooms/' . $id . '?' . http_build_query(['success' => 'Student removed successfully']));
            } else {
                return Response::redirect('/teacher/classrooms/' . $id . '?error=Failed to remove student');
            }
        } catch (\Exception $e) {
            return Response::redirect('/teacher/classrooms/' . $id . '?error=Error removing student: ' . $e->getMessage());
        }
    }

    private function getTeacherDashboardData(int $userId): array
    {
        $classroomModel = new Classroom();
        $activityModel  = new ActivityLogs();

        // Get teacher's classrooms
        $myClassrooms    = $classroomModel->getByTeacher($userId);
        $totalClassrooms = count($myClassrooms);

        // Calculate total students across all classrooms
        $totalStudents = 0;
        foreach ($myClassrooms as $classroom) {
            $students = $classroomModel->getStudents($classroom['id']);
            $totalStudents += count($students);
        }

        // Get recent activity for this teacher
        $recentActivity = $activityModel->getRecentActivities($userId, 5);

        // TODO: Get assignments and pending submissions when Assignment model is ready
        $totalAssignments   = 0;
        $pendingSubmissions = 0;

        return [
            'stats'           => [
                'total_classrooms'    => $totalClassrooms,
                'total_students'      => $totalStudents,
                'total_assignments'   => $totalAssignments,
                'pending_submissions' => $pendingSubmissions,
            ],
            'my_classrooms'   => array_slice($myClassrooms, 0, 5), // Show only first 5 for dashboard
            'recent_activity' => $recentActivity,
        ];
    }

    private function getTeacherClassroomsWithStats(int $userId): array
    {
        $classroomModel = new Classroom();
        $classrooms     = $classroomModel->getByTeacher($userId);

        // Add student count to each classroom
        foreach ($classrooms as &$classroom) {
            $students                   = $classroomModel->getStudents($classroom['id']);
            $classroom['student_count'] = count($students);

            // TODO: Add assignment count when Assignment model is ready
            $classroom['assignment_count'] = 0;
        }

        return $classrooms;
    }
}
