<?php
namespace App\Controllers;

use App\Models\Classroom;
use App\Models\User;
use App\Models\UserProfile;
use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Response;
use Gerald\Framework\Http\Session;

class ClassroomController extends AbstractController
{
    // ===== VIEW METHODS (Frontend) =====
    
    // Admin - List all classrooms
    public function index(): Response
    {
        $session = new Session();
        if (!$session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $userId = $session->get('user_id');
        $userModel = new User();
        $userData = $userModel->find($userId);

        if (($userData['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        // Get all classrooms with pagination
        $page = $this->request->getQuery('page', 1);
        $perPage = 20;

        $classroomModel = new Classroom();
        $classrooms = $classroomModel->getAllWithPagination((int) $page, $perPage);
        $totalClassrooms = $classroomModel->count();
        $totalPages = ceil($totalClassrooms / $perPage);

        // Get messages from URL parameters
        $successMessage = $this->request->getQuery('success');
        $errorMessage = $this->request->getQuery('error');

        return $this->render('admin/classrooms/index.html.twig', [
            'classrooms' => $classrooms,
            'currentPage' => (int) $page,
            'totalPages' => $totalPages,
            'totalClassrooms' => $totalClassrooms,
            'user_role' => $session->get('user_role'),
            'first_name' => $session->get('first_name'),
            'success_message' => $successMessage,
            'error_message' => $errorMessage,
            'current_route' => '/admin/classrooms',
            'session' => $session->all(),
        ]);
    }

    // Admin - Show create classroom form
    public function create(): Response
    {
        $session = new Session();
        if (!$session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $userId = $session->get('user_id');
        $userModel = new User();
        $userData = $userModel->find($userId);

        if (($userData['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        // Get all active teachers for the dropdown
        $teachers = $userModel->findByRoleWithDetails('teacher');

        return $this->render('admin/classrooms/create.html.twig', [
            'teachers' => $teachers,
            'user_role' => $session->get('user_role'),
            'first_name' => $session->get('first_name'),
        ]);
    }

    // Admin - Show classroom details
    public function show(string $id): Response
    {
        $session = new Session();
        if (!$session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $userId = $session->get('user_id');
        $userModel = new User();
        $userData = $userModel->find($userId);

        if (($userData['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        $classroomModel = new Classroom();
        $classroom = $classroomModel->getWithTeacherDetails((int) $id);
        
        if (!$classroom) {
            return Response::redirect('/admin/classrooms?error=Classroom not found');
        }

        // Get classroom students with their profiles
        $students = $classroomModel->getStudentsWithProfiles((int) $id);
        $totalStudents = count($students);

        // Get available students (not in this classroom)
        $availableStudents = $classroomModel->getAvailableStudents((int) $id);

        return $this->render('admin/classrooms/show.html.twig', [
            'classroom' => $classroom,
            'students' => $students,
            'totalStudents' => $totalStudents,
            'availableStudents' => $availableStudents,
            'user_role' => $session->get('user_role'),
            'first_name' => $session->get('first_name'),
            'current_route' => '/admin/classrooms',
            'session' => $session->all(),
        ]);
    }

    // Admin - Show edit classroom form
    public function edit(string $id): Response
    {
        $session = new Session();
        if (!$session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $currentUserId = $session->get('user_id');
        $userModel = new User();
        $currentUser = $userModel->find($currentUserId);

        if (($currentUser['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        $classroomModel = new Classroom();
        $classroom = $classroomModel->find((int) $id);
        if (!$classroom) {
            return Response::redirect('/admin/classrooms?error=Classroom not found');
        }

        // Get all active teachers for the dropdown
        $teachers = $userModel->findByRoleWithDetails('teacher');

        // Get error message from URL parameters
        $errorMessage = $this->request->getQuery('error');

        return $this->render('admin/classrooms/edit.html.twig', [
            'classroom' => $classroom,
            'teachers' => $teachers,
            'user_role' => $session->get('user_role'),
            'first_name' => $session->get('first_name'),

            'error_message' => $errorMessage,
        ]);
    }

    // ===== ACTION METHODS (Backend) =====

    // Admin - Store new classroom
    public function store(): Response
    {
        $session = new Session();
        if (!$session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $userId = $session->get('user_id');
        $userModel = new User();
        $userData = $userModel->find($userId);

        if (($userData['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        $teacherId = $this->request->getPost('teacher_id');
        $name = $this->request->getPost('name');
        $code = $this->request->getPost('code');
        $description = $this->request->getPost('description');

        if (!$teacherId || !$name || !$code) {
            return Response::redirect('/admin/classrooms/create?error=Teacher, name, and code are required');
        }

        try {
            $classroomModel = new Classroom();
            
            // Check if code already exists
            $existingClassroom = $classroomModel->findByCode($code);
            if ($existingClassroom) {
                return Response::redirect('/admin/classrooms/create?error=Classroom code already exists');
            }

            // Verify teacher exists and is active
            $teacher = $userModel->getUserData((int) $teacherId);
            if (!$teacher || $teacher['role'] !== 'teacher' || $teacher['status'] !== 'active') {
                return Response::redirect('/admin/classrooms/create?error=Invalid teacher selected');
            }

            // Create classroom
            $classroomModel->create([
                'teacher_id' => (int) $teacherId,
                'name' => $name,
                'code' => $code,
                'description' => $description,
            ]);

            return Response::redirect('/admin/classrooms?' . http_build_query(['success' => 'Classroom created successfully']));
        } catch (\Exception $e) {
            return Response::redirect('/admin/classrooms/create?error=Error creating classroom: ' . $e->getMessage());
        }
    }

    // Admin - Update classroom
    public function update(string $id): Response
    {
        $session = new Session();
        if (!$session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $currentUserId = $session->get('user_id');
        $userModel = new User();
        $currentUser = $userModel->find($currentUserId);

        if (($currentUser['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        $classroomModel = new Classroom();
        $classroom = $classroomModel->find((int) $id);
        if (!$classroom) {
            return Response::redirect('/admin/classrooms?error=Classroom not found');
        }

        $teacherId = $this->request->getPost('teacher_id');
        $name = $this->request->getPost('name');
        $code = $this->request->getPost('code');
        $description = $this->request->getPost('description');

        if (!$teacherId || !$name || !$code) {
            return Response::redirect('/admin/classrooms/' . $id . '/edit?error=Teacher, name, and code are required');
        }

        try {
            // Check if code already exists (excluding current classroom)
            $existingClassroom = $classroomModel->findByCode($code);
            if ($existingClassroom && $existingClassroom['id'] != $id) {
                return Response::redirect('/admin/classrooms/' . $id . '/edit?error=Classroom code already exists');
            }

            // Verify teacher exists and is active
            $teacher = $userModel->getUserData((int) $teacherId);
            if (!$teacher || $teacher['role'] !== 'teacher' || $teacher['status'] !== 'active') {
                return Response::redirect('/admin/classrooms/' . $id . '/edit?error=Invalid teacher selected');
            }

            // Update classroom
            $classroomModel->updateClassroom((int) $id, [
                'teacher_id' => (int) $teacherId,
                'name' => $name,
                'code' => $code,
                'description' => $description,
            ]);

            return Response::redirect('/admin/classrooms?' . http_build_query(['success' => 'Classroom updated successfully']));
        } catch (\Exception $e) {
            return Response::redirect('/admin/classrooms/' . $id . '/edit?error=Error updating classroom: ' . $e->getMessage());
        }
    }

    // Admin - Delete classroom
    public function delete(string $id): Response
    {
        $session = new Session();
        if (!$session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $currentUserId = $session->get('user_id');
        $userModel = new User();
        $currentUser = $userModel->find($currentUserId);

        if (($currentUser['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        $classroomModel = new Classroom();
        $classroom = $classroomModel->find((int) $id);
        if (!$classroom) {
            return Response::redirect('/admin/classrooms?error=Classroom not found');
        }

        try {
            // Note: You might want to implement soft delete for classrooms too
            // For now, we'll do a hard delete but check for dependencies first
            
            // Check if classroom has students
            $students = $classroomModel->getStudents((int) $id);
            if (!empty($students)) {
                return Response::redirect('/admin/classrooms?error=Cannot delete classroom with enrolled students. Remove students first.');
            }

            // Delete classroom
            $classroomModel->delete((int) $id);

            return Response::redirect('/admin/classrooms?' . http_build_query(['success' => 'Classroom deleted successfully']));
        } catch (\Exception $e) {
            return Response::redirect('/admin/classrooms?error=Error deleting classroom: ' . $e->getMessage());
        }
    }

    // Admin - Add student to classroom
    public function addStudent(string $id): Response
    {
        $session = new Session();
        if (!$session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $currentUserId = $session->get('user_id');
        $userModel = new User();
        $currentUser = $userModel->find($currentUserId);

        if (($currentUser['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        $studentId = $this->request->getPost('student_id');
        if (!$studentId) {
            return Response::redirect('/admin/classrooms/' . $id . '?error=Please select a student');
        }

        try {
            $classroomModel = new Classroom();
            
            // Verify student exists and is active
            $student = $userModel->getUserData((int) $studentId);
            if (!$student || $student['role'] !== 'student' || $student['status'] !== 'active') {
                return Response::redirect('/admin/classrooms/' . $id . '?error=Invalid student selected');
            }

            // Add student to classroom
            $classroomModel->addStudent((int) $id, (int) $studentId);

            return Response::redirect('/admin/classrooms/' . $id . '?' . http_build_query(['success' => 'Student added to classroom successfully']));
        } catch (\Exception $e) {
            return Response::redirect('/admin/classrooms/' . $id . '?error=Error adding student: ' . $e->getMessage());
        }
    }

    // Admin - Remove student from classroom
    public function removeStudent(string $id, string $studentId): Response
    {
        $session = new Session();
        if (!$session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $currentUserId = $session->get('user_id');
        $userModel = new User();
        $currentUser = $userModel->find($currentUserId);

        if (($currentUser['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        try {
            $classroomModel = new Classroom();
            $classroomModel->removeStudent((int) $id, (int) $studentId);

            return Response::redirect('/admin/classrooms/' . $id . '?' . http_build_query(['success' => 'Student removed from classroom successfully']));
        } catch (\Exception $e) {
            return Response::redirect('/admin/classrooms/' . $id . '?error=Error removing student: ' . $e->getMessage());
        }
    }

    // Legacy method - keeping for backward compatibility
    public function viewClassroom(): Response
    {
        return $this->render('student/classes.html.twig');
    }
}