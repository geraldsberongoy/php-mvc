<?php
namespace App\Controllers\Teacher;

use App\Models\ActivityLogs;
use App\Models\Classroom;

class DashboardController extends BaseTeacherController
{
    public function index()
    {
        // Get real dashboard data for teacher
        $dashboardData = $this->getTeacherDashboardData($this->userId);

        return $this->renderTeacher('teacher/dashboard.html.twig', [
            'dashboard_data' => $dashboardData,
            'current_route'  => '/teacher/dashboard',
        ]);
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
            $students = $classroomModel->getEnrolledStudents($classroom['id']);
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
}
