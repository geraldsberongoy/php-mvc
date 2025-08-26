<?php
namespace App\Controllers;

use App\Models\User;
use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Response;
use Gerald\Framework\Http\Session;

class HomeController extends AbstractController
{
    public function index(): Response
    {
        $session = new Session();
        return $this->render('home.html.twig', [
            'session' => $session->all(),
        ]);
    }

    public function showDashboard(): Response
    {
        $session = new Session();
        if ($session->has('user_id')) {
            $userId    = $session->get('user_id');
            $userModel = new User();
            $userData  = $userModel->find($userId);
            $userRole  = $userData['role'] ?? 'student';

            // Redirect based on user role
            switch ($userRole) {
                case 'admin':
                    return Response::redirect('/admin/dashboard');
                case 'teacher':
                    return Response::redirect('/teacher/dashboard');
                case 'student':
                    return Response::redirect('/student/dashboard');
                default:
                    return Response::redirect('/student/dashboard');
            }
        }

        return Response::redirect('/login');
    }

    public function adminDashboard(): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        // Check if user is admin
        if (($userData['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        // Get real dashboard data for admin
        $dashboardData = $this->getAdminDashboardData();

        return $this->render('admin/dashboard.html.twig', [
            'user_id'        => $userId,
            'first_name'     => $session->get('first_name') ?? 'Admin',
            'user_role'      => 'admin',
            'dashboard_data' => $dashboardData,
            'session'        => $session->all(),
        ]);
    }

    public function teacherDashboard(): Response
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

        // Get dashboard data for teacher (placeholder for now)
        $dashboardData = $this->getTeacherDashboardData($userId);

        return $this->render('teacher/dashboard.html.twig', [
            'user_id'        => $userId,
            'first_name'     => $session->get('first_name') ?? 'Teacher',
            'user_role'      => 'teacher',
            'dashboard_data' => $dashboardData,
            'session'        => $session->all(),
        ]);
    }

    public function studentDashboard(): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        // Check if user is student
        if (($userData['role'] ?? 'student') !== 'student') {
            return Response::redirect('/dashboard');
        }

        // Get dashboard data for student (placeholder for now)
        $dashboardData = $this->getStudentDashboardData($userId);

        return $this->render('student/dashboard.html.twig', [
            'user_id'        => $userId,
            'first_name'     => $session->get('first_name') ?? 'Student',
            'user_role'      => 'student',
            'dashboard_data' => $dashboardData,
            'session'        => $session->all(),
        ]);
    }

    private function getAdminDashboardData(): array
    {
        $userModel = new User();

        // Get real stats from database
        $totalUsers   = $userModel->count();
        $teacherCount = $userModel->countByRole('teacher');
        $studentCount = $userModel->countByRole('student');

                              // TODO: Get classrooms count when Classroom model is ready
        $totalClassrooms = 0; // placeholder

        // Get recent activity from activity logs
        // TODO: Implement when ActivityLogs model is fully ready
        $recentActivity = [
            [
                'action'      => 'user_registered',
                'description' => 'New user registered in the system',
                'created_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        return [
            'stats'           => [
                'total_users'      => $totalUsers,
                'total_teachers'   => $teacherCount,
                'total_students'   => $studentCount,
                'total_classrooms' => $totalClassrooms,
            ],
            'recent_activity' => $recentActivity,
        ];
    }

    private function getTeacherDashboardData(int $userId): array
    {
        // TODO: Implement when Classroom and Assignment models are ready
        return [
            'stats'               => [
                'total_classrooms'    => 0,
                'total_students'      => 0,
                'pending_submissions' => 0,
                'assignments_created' => 0,
            ],
            'my_classrooms'       => [],
            'pending_submissions' => [],
            'recent_activity'     => [],
        ];
    }

    private function getStudentDashboardData(int $userId): array
    {
        // TODO: Implement when Classroom and Assignment models are ready
        return [
            'stats'              => [
                'enrolled_classrooms'   => 0,
                'completed_assignments' => 0,
                'pending_assignments'   => 0,
                'average_grade'         => 0,
            ],
            'recent_classrooms'  => [],
            'recent_assignments' => [],
            'recent_activity'    => [],
        ];
    }
}
