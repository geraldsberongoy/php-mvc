<?php
namespace App\Controllers\Student;

use App\Models\Classroom;
use Gerald\Framework\Http\Response;

class ClassroomController extends BaseStudentController
{
    public function index(): Response
    {
        $classroomModel = new Classroom();
        $myClassrooms   = $classroomModel->getByStudent($this->userId);

        // Get messages from URL parameters
        $successMessage = $this->request->getQuery('success');
        $errorMessage   = $this->request->getQuery('error');

        return $this->renderStudent('student/classes.html.twig', [
            'classrooms'      => $myClassrooms,
            'success_message' => $successMessage,
            'error_message'   => $errorMessage,
            'current_route'   => '/student/classes',
        ]);
    }

    public function show(string $id): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->getWithTeacherDetails((int) $id);

        if (! $classroom) {
            return Response::redirect('/student/classes?error=Classroom not found');
        }

        // Check if student is enrolled in this classroom
        $students   = $classroomModel->getStudents((int) $id);
        $isEnrolled = false;
        foreach ($students as $student) {
            if ($student['id'] == $this->userId) {
                $isEnrolled = true;
                break;
            }
        }

        if (! $isEnrolled) {
            return Response::redirect('/student/classes?error=Access denied');
        }

        // Get classmates
        $classmates = $classroomModel->getStudentsWithProfiles((int) $id);

                           // TODO: Get assignments for this classroom when Assignment model is ready
        $assignments = []; // Placeholder

        return $this->renderStudent('student/classrooms/show.html.twig', [
            'classroom'     => $classroom,
            'classmates'    => $classmates,
            'assignments'   => $assignments,
            'current_route' => '/student/classes',
        ]);
    }

    public function join(): Response
    {
        $code = $this->request->getPost('code');
        if (! $code) {
            return Response::redirect('/student/classes?error=Classroom code is required');
        }

        try {
            $classroomModel = new Classroom();
            $classroom      = $classroomModel->findByCode($code);

            if (! $classroom) {
                return Response::redirect('/student/classes?error=Invalid classroom code');
            }

            // Check if already enrolled
            $students = $classroomModel->getStudents($classroom['id']);
            foreach ($students as $student) {
                if ($student['id'] == $this->userId) {
                    return Response::redirect('/student/classes?error=You are already enrolled in this classroom');
                }
            }

            // Enroll student
            $success = $classroomModel->addStudent($classroom['id'], $this->userId);

            if ($success) {
                return Response::redirect('/student/classes?' . http_build_query(['success' => 'Successfully joined classroom: ' . $classroom['name']]));
            } else {
                return Response::redirect('/student/classes?error=Failed to join classroom');
            }
        } catch (\Exception $e) {
            return Response::redirect('/student/classes?error=Error joining classroom: ' . $e->getMessage());
        }
    }

    public function leave(string $id): Response
    {
        try {
            $classroomModel = new Classroom();
            $classroom      = $classroomModel->find((int) $id);

            if (! $classroom || ! is_array($classroom)) {
                return Response::redirect('/student/classes?error=Classroom not found');
            }

            // Check if student is enrolled
            $students   = $classroomModel->getStudents((int) $id);
            $isEnrolled = false;
            foreach ($students as $student) {
                if ($student['id'] == $this->userId) {
                    $isEnrolled = true;
                    break;
                }
            }

            if (! $isEnrolled) {
                return Response::redirect('/student/classes?error=You are not enrolled in this classroom');
            }

            // Remove student from classroom
            $success = $classroomModel->removeStudent((int) $id, $this->userId);

            if ($success) {
                return Response::redirect('/student/classes?' . http_build_query(['success' => 'Successfully left classroom: ' . $classroom['name']]));
            } else {
                return Response::redirect('/student/classes?error=Failed to leave classroom');
            }
        } catch (\Exception $e) {
            return Response::redirect('/student/classes?error=Error leaving classroom: ' . $e->getMessage());
        }
    }
}
