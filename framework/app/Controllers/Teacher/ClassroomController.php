<?php
namespace App\Controllers\Teacher;

use App\Controllers\ActivityLogsController;
use App\Models\ActivityLogs;
use App\Models\Classroom;
use App\Models\ClassroomPost;
use App\Models\User;
use Gerald\Framework\Http\Response;

class ClassroomController extends BaseTeacherController
{
    public function index(): Response
    {
        $classroomModel = new Classroom();
        $classrooms     = $this->getTeacherClassroomsWithStats($this->userId);

        // Get messages from URL parameters
        $successMessage = $this->request->getQuery('success');
        $errorMessage   = $this->request->getQuery('error');

        return $this->renderTeacher('teacher/classrooms/index.html.twig', [
            'classrooms'      => $classrooms,
            'success_message' => $successMessage,
            'error_message'   => $errorMessage,
            'current_route'   => '/teacher/classrooms',
        ]);
    }

    public function create(): Response
    {
        return $this->renderTeacher('teacher/classrooms/create.html.twig', [
            'current_route' => '/teacher/classrooms',
        ]);
    }

    public function show(string $id): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->getWithTeacherDetails((int) $id);

        if (! $classroom) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $students          = $classroomModel->getStudentsWithProfiles((int) $id);
        $availableStudents = $classroomModel->getAvailableStudents((int) $id);

        // Get posts for the stream tab
        $posts = $this->getClassroomPosts((int) $id);

                           // Get assignments for this classroom (when Assignment model is ready)
        $assignments = []; // Placeholder

        // Get messages from URL parameters
        $successMessage = $this->request->getQuery('success');
        $errorMessage   = $this->request->getQuery('error');

        return $this->renderTeacher('teacher/classrooms/show.html.twig', [
            'classroom'          => $classroom,
            'students'           => $students,
            'available_students' => $availableStudents,
            'posts'              => $posts,
            'assignments'        => $assignments,
            'success_message'    => $successMessage,
            'error_message'      => $errorMessage,
            'current_route'      => '/teacher/classrooms',
        ]);
    }

    public function edit(string $id): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $id);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        // Get error message from URL parameters
        $errorMessage = $this->request->getQuery('error');

        return $this->renderTeacher('teacher/classrooms/edit.html.twig', [
            'classroom'     => $classroom,
            'error_message' => $errorMessage,
            'current_route' => '/teacher/classrooms',
        ]);
    }

    // ACTIONS

    public function store(): Response
    {
        $name        = $this->request->getPost('name') ?? null;
        $code        = $this->request->getPost('code') ?? null;
        $description = $this->request->getPost('description') ?? null;

        if (! $name || ! $code) {
            return $this->renderTeacher('teacher/classrooms/create.html.twig', [
                'error' => 'Name and code are required',
            ]);
        }

        try {
            $classroomModel = new Classroom();

            // Check if code already exists
            $existingClassroom = $classroomModel->findByCode($code);
            if ($existingClassroom) {
                return $this->renderTeacher('teacher/classrooms/create.html.twig', [
                    'error' => 'Classroom code already exists',
                ]);
            }

            // Create classroom
            $classroomId = $classroomModel->create([
                'teacher_id'  => $this->userId,
                'name'        => $name,
                'code'        => $code,
                'description' => $description,
            ]);

            // Log activity
            $activityModel = new ActivityLogs();
            $activityModel->log($this->userId, 'created_classroom',
                "Created classroom '{$name}' with code '{$code}'",
                ActivityLogsController::getUserIP());

            return Response::redirect('/teacher/classrooms?' . http_build_query(['success' => 'Classroom created successfully']));
        } catch (\Exception $e) {
            return $this->renderTeacher('teacher/classrooms/create.html.twig', [
                'error' => 'Error creating classroom: ' . $e->getMessage(),
            ]);
        }
    }

    public function update(string $id): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $id);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
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
            $activityModel->log($this->userId, 'updated_classroom',
                "Updated classroom '{$name}' (ID: {$id})",
                ActivityLogsController::getUserIP());

            return Response::redirect('/teacher/classrooms?' . http_build_query(['success' => 'Classroom updated successfully']));
        } catch (\Exception $e) {
            return Response::redirect('/teacher/classrooms/' . $id . '/edit?error=Error updating classroom: ' . $e->getMessage());
        }
    }

    public function addStudent(string $id): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $id);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
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
                $userModel   = new User();
                $studentData = $userModel->findWithDetails((int) $studentId);
                $studentName = ($studentData['first_name'] ?? '') . ' ' . ($studentData['last_name'] ?? '');

                // Log activity
                $activityModel = new ActivityLogs();
                $activityModel->log($this->userId, 'added_student_to_classroom',
                    "Added student '{$studentName}' to classroom '{$classroom['name']}'",
                    ActivityLogsController::getUserIP());

                return Response::redirect('/teacher/classrooms/' . $id . '?' . http_build_query(['success' => 'Student added successfully']));
            } else {
                return Response::redirect('/teacher/classrooms/' . $id . '?error=Failed to add student');
            }
        } catch (\Exception $e) {
            return Response::redirect('/teacher/classrooms/' . $id . '?error=Error adding student: ' . $e->getMessage());
        }
    }

    public function removeStudent(string $id, string $studentId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $id);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        try {
            // Get student name for logging before removal
            $userModel   = new User();
            $studentData = $userModel->findWithDetails((int) $studentId);
            $studentName = ($studentData['first_name'] ?? '') . ' ' . ($studentData['last_name'] ?? '');

            // Remove student from classroom
            $success = $classroomModel->removeStudent((int) $id, (int) $studentId);

            if ($success) {
                // Log activity
                $activityModel = new ActivityLogs();
                $activityModel->log($this->userId, 'removed_student_from_classroom',
                    "Removed student '{$studentName}' from classroom '{$classroom['name']}'",
                    ActivityLogsController::getUserIP());

                return Response::redirect('/teacher/classrooms/' . $id . '?' . http_build_query(['success' => 'Student removed successfully']));
            } else {
                return Response::redirect('/teacher/classrooms/' . $id . '?error=Failed to remove student');
            }
        } catch (\Exception $e) {
            return Response::redirect('/teacher/classrooms/' . $id . '?error=Error removing student: ' . $e->getMessage());
        }
    }

    private function getTeacherClassroomsWithStats(int $userId): array
    {
        $classroomModel = new Classroom();
        $classrooms     = $classroomModel->getByTeacher($userId);

        // Add student count to each classroom
        foreach ($classrooms as &$classroom) {
            $students                   = $classroomModel->getEnrolledStudents($classroom['id']);
            $classroom['student_count'] = count($students);

            // TODO: Add assignment count when Assignment model is ready
            $classroom['assignment_count'] = 0;
        }

        return $classrooms;
    }

    private function getClassroomPosts(int $classroomId): array
    {
        $postModel = new ClassroomPost();
        return $postModel->getByClassroom($classroomId);
    }
}
