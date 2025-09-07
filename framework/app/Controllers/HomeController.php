<?php
namespace App\Controllers;

use App\Models\ActivityLogs;
use App\Models\Classroom;
use App\Models\User;
use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Response;
use Gerald\Framework\Http\Session;

class HomeController extends AbstractController
{

    // VIEWS //
    public function index(): Response
    // Show the landing page or redirect to dashboard if logged in
    {
        $session = new Session();
        if ($session->has('user_id')) {
            return Response::redirect('/dashboard');
        }
        return $this->render('landing.html.twig', [
            'session' => $session->all(),
        ]);
    }
    
    public function redirectDashboard(): Response
    // Redirect function to user-specific dashboard
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
}
