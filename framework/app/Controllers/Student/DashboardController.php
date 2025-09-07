<?php
namespace App\Controllers\Student;

use App\Models\Classroom;

class DashboardController extends BaseStudentController
{
    public function index()
    {
        // Get student dashboard data
        $dashboardData = $this->getStudentDashboardData($this->userId);

        return $this->renderStudent('student/dashboard.html.twig', [
            'dashboard_data' => $dashboardData,
            'current_route'  => '/student/dashboard',
        ]);
    }

    private function getStudentDashboardData(int $userId): array
    {
        $classroomModel = new Classroom();

        // Get student's enrolled classrooms
        $myClassrooms    = $classroomModel->getByStudent($userId);
        $totalClassrooms = count($myClassrooms);

        // TODO: Get assignments and submissions when Assignment model is ready
        $totalAssignments     = 0;
        $completedAssignments = 0;
        $pendingAssignments   = 0;

        return [
            'stats'         => [
                'total_classrooms'      => $totalClassrooms,
                'total_assignments'     => $totalAssignments,
                'completed_assignments' => $completedAssignments,
                'pending_assignments'   => $pendingAssignments,
            ],
            'my_classrooms' => array_slice($myClassrooms, 0, 5), // Show only first 5 for dashboard
        ];
    }
}
