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
        return $this->render('home.html.twig');
    }

    public function showDashboard(): Response
    {
        $session = new Session();
        if ($session->has('user_id')) {
            $userId    = $session->get('user_id');
            $userModel = new User();
            // $userRole  = $userModel->getUserRole($userId) ?? 'student';
            $userData = $userModel->find($userId);

            // Placeholder data based on schema - will be replaced with actual DB queries
            $dashboardData = $this->getDashboardData($userId, $userData['role'] ?? 'student');

            return $this->render('dashboard.html.twig', [
                'user_id'        => $userId,
                'first_name'     => $session->get('first_name') ?? 'Guest',
                'user_role'      => $userData['role'] ?? 'student',
                'dashboard_data' => $dashboardData,
            ]);
        }

        return Response::redirect('/');
    }

    // TODO: Replace this method with actual database queries when models are ready
    private function getDashboardData(int $userId, string $userRole): array
    {
        // Placeholder data based on your database schema
        if ($userRole === 'student') {
            return [
                'stats'              => [
                    'enrolled_classrooms'   => 5,
                    'completed_assignments' => 12,
                    'pending_assignments'   => 3,
                    'average_grade'         => 85,
                ],
                'recent_classrooms'  => [
                    [
                        'id'           => 1,
                        'name'         => 'Web Development Fundamentals',
                        'teacher_name' => 'Prof. Johnson',
                        'code'         => 'WEB101',
                        'progress'     => 75,
                        'color'        => 'blue',
                    ],
                    [
                        'id'           => 2,
                        'name'         => 'Database Management Systems',
                        'teacher_name' => 'Dr. Smith',
                        'code'         => 'DB201',
                        'progress'     => 60,
                        'color'        => 'green',
                    ],
                    [
                        'id'           => 3,
                        'name'         => 'Software Engineering',
                        'teacher_name' => 'Prof. Davis',
                        'code'         => 'SE301',
                        'progress'     => 30,
                        'color'        => 'purple',
                    ],
                ],
                'recent_assignments' => [
                    [
                        'id'             => 1,
                        'title'          => 'HTML/CSS Portfolio Project',
                        'classroom_name' => 'Web Development',
                        'due_date'       => '2025-08-28',
                        'status'         => 'pending',
                    ],
                    [
                        'id'             => 2,
                        'title'          => 'Database Design Assignment',
                        'classroom_name' => 'Database Management',
                        'due_date'       => '2025-08-30',
                        'status'         => 'submitted',
                    ],
                ],
                'recent_activity'    => [
                    [
                        'action'      => 'submitted_assignment',
                        'description' => 'Submitted "JavaScript Functions" assignment',
                        'created_at'  => '2025-08-24 14:30:00',
                    ],
                    [
                        'action'      => 'joined_classroom',
                        'description' => 'Enrolled in "Software Engineering"',
                        'created_at'  => '2025-08-23 09:15:00',
                    ],
                    [
                        'action'      => 'completed_assignment',
                        'description' => 'Completed "CSS Grid Layout" with grade 92',
                        'created_at'  => '2025-08-22 16:45:00',
                    ],
                ],
            ];
        } elseif ($userRole === 'teacher') {
            return [
                'stats'               => [
                    'total_classrooms'    => 3,
                    'total_students'      => 45,
                    'pending_submissions' => 8,
                    'assignments_created' => 15,
                ],
                'my_classrooms'       => [
                    [
                        'id'                => 1,
                        'name'              => 'Web Development Fundamentals',
                        'code'              => 'WEB101',
                        'student_count'     => 25,
                        'assignments_count' => 8,
                        'color'             => 'blue',
                    ],
                    [
                        'id'                => 2,
                        'name'              => 'Advanced JavaScript',
                        'code'              => 'JS201',
                        'student_count'     => 20,
                        'assignments_count' => 6,
                        'color'             => 'yellow',
                    ],
                ],
                'pending_submissions' => [
                    [
                        'student_name'     => 'John Doe',
                        'assignment_title' => 'React Components',
                        'classroom_name'   => 'Advanced JavaScript',
                        'due_date'         => '2025-08-26',
                    ],
                    [
                        'student_name'     => 'Jane Smith',
                        'assignment_title' => 'HTML Portfolio',
                        'classroom_name'   => 'Web Development',
                        'due_date'         => '2025-08-27',
                    ],
                ],
                'recent_activity'     => [
                    [
                        'action'      => 'graded_assignment',
                        'description' => 'Graded 5 submissions for "CSS Flexbox"',
                        'created_at'  => '2025-08-24 11:20:00',
                    ],
                    [
                        'action'      => 'created_assignment',
                        'description' => 'Created new assignment "React Hooks"',
                        'created_at'  => '2025-08-23 15:30:00',
                    ],
                ],
            ];
        } else { // admin
            return [
                'stats'           => [
                    'total_users'      => 156,
                    'total_teachers'   => 12,
                    'total_students'   => 143,
                    'total_classrooms' => 18,
                ],
                'recent_activity' => [
                    [
                        'action'      => 'user_registered',
                        'description' => 'New student registration: Alice Johnson',
                        'created_at'  => '2025-08-24 10:15:00',
                    ],
                    [
                        'action'      => 'classroom_created',
                        'description' => 'Teacher created classroom "Python Basics"',
                        'created_at'  => '2025-08-23 14:20:00',
                    ],
                ],
            ];
        }
    }
}
