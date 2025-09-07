<?php
namespace App\Controllers\Admin;

use App\Models\ActivityLogs;
use App\Models\Classroom;
use App\Models\User;

class DashboardController extends BaseAdminController
{
    public function index()
    {
        // Get real dashboard data for admin
        $dashboardData = $this->getAdminDashboardData();

        return $this->renderAdmin('admin/dashboard.html.twig', [
            'dashboard_data' => $dashboardData,
            'current_route'  => '/admin/dashboard',
        ]);
    }

    private function getAdminDashboardData(): array
    {
        $userModel      = new User();
        $classroomModel = new Classroom();
        $activityModel  = new ActivityLogs();

        // Get real stats from database
        $totalUsers      = $userModel->count();
        $teacherCount    = $userModel->countByRole('teacher');
        $studentCount    = $userModel->countByRole('student');
        $totalClassrooms = $classroomModel->count();

        return [
            'stats' => [
                'total_users'      => $totalUsers,
                'total_teachers'   => $teacherCount,
                'total_students'   => $studentCount,
                'total_classrooms' => $totalClassrooms,
            ],
        ];
    }
}
